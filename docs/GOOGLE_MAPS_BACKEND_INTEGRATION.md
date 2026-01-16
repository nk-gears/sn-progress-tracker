# Google Maps Backend API Integration Guide

## Overview

This document outlines how to integrate Google Maps API in the backend to enhance the "Near Me" functionality by:
1. Fetching accurate distances from user's GPS location to centers
2. Sorting centers by proximity
3. Providing more accurate "km away" details

## Current Implementation

### Frontend (Current)
**File:** `web-app/src/components/CentreFinder.vue`

Currently uses Haversine formula in JavaScript:
```typescript
function calculateDistance(lat1: number, lon1: number, lat2: number, lon2: number): number {
  const R = 6371 // Radius of Earth in km
  const dLat = (lat2 - lat1) * Math.PI / 180
  const dLon = (lon2 - lon1) * Math.PI / 180
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2)
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  return R * c
}
```

**Limitations:**
- Calculates straight-line distance ("as the crow flies")
- Doesn't account for actual road routes
- Less accurate for navigation purposes

## Proposed Solution: Backend Google Maps Integration

### Option 1: Google Maps Distance Matrix API (Recommended)

#### Benefits:
- ✅ Accurate road distances
- ✅ Travel time estimates
- ✅ Multiple origins and destinations in single request
- ✅ Handles traffic data
- ✅ Better for "km away" display

#### Setup Steps:

##### 1. Enable Google Maps Distance Matrix API

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Enable "Distance Matrix API"
3. Use existing API key or create new one
4. Set billing (offers $200 free credits monthly)

##### 2. Update Backend API

**File:** `backend/api.php`

Add new endpoint for getting nearby centers:

```php
case 'nearby-centres':
    handleNearbyCentres();
    break;
```

**Implementation:**

```php
function handleNearbyCentres() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $missing = validateRequired($input, ['latitude', 'longitude']);

    if (!empty($missing)) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)], 400);
        return;
    }

    $userLat = (float)$input['latitude'];
    $userLng = (float)$input['longitude'];
    $maxDistance = isset($input['max_distance']) ? (int)$input['max_distance'] : 50; // km

    try {
        // Get all centres from database
        $centres = fetchAll("
            SELECT id, name, address, district, state, latitude, longitude, phone
            FROM medt_branches
            WHERE latitude IS NOT NULL AND longitude IS NOT NULL
            ORDER BY name
        ");

        // Get Google Maps API key from environment or config
        $googleApiKey = getenv('GOOGLE_MAPS_API_KEY') ?: 'AIzaSyB80mIsZ2S4llsDA3rzssAqjvDGvZgFLv8';

        // Build destinations string (max 25 destinations per request)
        $destinations = [];
        $centreMap = [];

        foreach ($centres as $index => $centre) {
            $destinations[] = $centre['latitude'] . ',' . $centre['longitude'];
            $centreMap[$index] = $centre;
        }

        // Process in batches of 25 (Google Maps API limit)
        $batchSize = 25;
        $allCentresWithDistance = [];

        for ($i = 0; $i < count($destinations); $i += $batchSize) {
            $batch = array_slice($destinations, $i, $batchSize);
            $batchCentres = array_slice($centreMap, $i, $batchSize);

            $destinationsString = implode('|', $batch);
            $origin = $userLat . ',' . $userLng;

            // Call Google Maps Distance Matrix API
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?" . http_build_query([
                'origins' => $origin,
                'destinations' => $destinationsString,
                'key' => $googleApiKey,
                'units' => 'metric'
            ]);

            $response = @file_get_contents($url);

            if ($response === false) {
                error_log('Google Maps API request failed');
                continue;
            }

            $data = json_decode($response, true);

            if ($data['status'] !== 'OK') {
                error_log('Google Maps API error: ' . $data['status']);
                continue;
            }

            // Process results
            $elements = $data['rows'][0]['elements'];

            foreach ($elements as $index => $element) {
                $centreIndex = $i + $index;

                if ($element['status'] === 'OK') {
                    $distanceKm = $element['distance']['value'] / 1000; // Convert meters to km

                    if ($distanceKm <= $maxDistance) {
                        $centre = $batchCentres[$index];
                        $centre['distance'] = round($distanceKm, 1);
                        $centre['distance_text'] = $element['distance']['text'];
                        $centre['duration_text'] = $element['duration']['text'];
                        $allCentresWithDistance[] = $centre;
                    }
                }
            }
        }

        // Sort by distance
        usort($allCentresWithDistance, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        sendResponse([
            'success' => true,
            'centres' => $allCentresWithDistance,
            'count' => count($allCentresWithDistance)
        ]);

    } catch (Exception $e) {
        error_log('Nearby centres error: ' . $e->getMessage());
        sendResponse(['success' => false, 'message' => 'Failed to fetch nearby centres'], 500);
    }
}
```

##### 3. Update Frontend to Use New Endpoint

**File:** `web-app/src/components/CentreFinder.vue`

Update the `getCurrentLocation` function:

```typescript
async function getCurrentLocation() {
  if (!navigator.geolocation) {
    errorMessage.value = 'Geolocation is not supported by your browser'
    return
  }

  isLoadingLocation.value = true
  errorMessage.value = ''

  try {
    const position = await new Promise<GeolocationPosition>((resolve, reject) => {
      navigator.geolocation.getCurrentPosition(resolve, reject, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
      })
    })

    userLocation.value = {
      lat: position.coords.latitude,
      lng: position.coords.longitude
    }

    // Fetch centres from backend with Google Maps distances
    await fetchNearbyCentresFromBackend(
      position.coords.latitude,
      position.coords.longitude
    )

  } catch (error) {
    errorMessage.value = 'Unable to get your location. Please check permissions.'
    // Fallback to frontend calculation if backend fails
  } finally {
    isLoadingLocation.value = false
  }
}

// New function to fetch from backend
async function fetchNearbyCentresFromBackend(lat: number, lng: number) {
  try {
    const response = await fetch(`${API_BASE_URL}/nearby-centres`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        latitude: lat,
        longitude: lng,
        max_distance: maxDistance.value
      })
    })

    const data = await response.json()

    if (data.success && data.centres) {
      // Update centres with backend data
      filteredCenters.value = data.centres
    } else {
      // Fallback to frontend calculation
      console.warn('Backend failed, using frontend distance calculation')
    }
  } catch (error) {
    console.error('Error fetching nearby centres:', error)
    // Fallback to frontend calculation
  }
}
```

##### 4. Environment Configuration

**File:** `backend/config.php`

Add Google Maps API key:

```php
// Google Maps API Key
$googleMapsApiKey = getenv('GOOGLE_MAPS_API_KEY') ?: 'YOUR_API_KEY_HERE';

// Make it available globally
define('GOOGLE_MAPS_API_KEY', $googleMapsApiKey);
```

**Production (.env or environment variables):**
```bash
GOOGLE_MAPS_API_KEY=your_actual_production_key_here
```

### Option 2: Geocoding API with Haversine (Budget Alternative)

If Distance Matrix API is too expensive, use Geocoding API to validate addresses and continue using Haversine formula:

```php
function validateCentreLocation($centreId) {
    $centre = fetchRow("SELECT address FROM medt_branches WHERE id = ?", [$centreId], 'i');

    $url = "https://maps.googleapis.com/maps/api/geocode/json?" . http_build_query([
        'address' => $centre['address'],
        'key' => GOOGLE_MAPS_API_KEY
    ]);

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data['status'] === 'OK' && !empty($data['results'])) {
        $location = $data['results'][0]['geometry']['location'];

        // Update database with accurate coordinates
        executeQuery(
            "UPDATE medt_branches SET latitude = ?, longitude = ? WHERE id = ?",
            [$location['lat'], $location['lng'], $centreId],
            'ddi'
        );
    }
}
```

## Cost Comparison

### Google Maps Distance Matrix API Pricing

**Free Tier:**
- $200 monthly credit (≈ 40,000 requests)
- $5 per 1,000 requests after free tier

**Cost per User:**
- If 25 centres: 1 batch = 1 request
- If 100 centres: 4 batches = 4 requests
- Average: 2 requests per user search

**Monthly Estimates:**
| Users/Month | Searches | Requests | Cost |
|-------------|----------|----------|------|
| 1,000 | 2,000 | 4,000 | Free |
| 5,000 | 10,000 | 20,000 | Free |
| 10,000 | 20,000 | 40,000 | Free |
| 20,000 | 40,000 | 80,000 | $200 |
| 50,000 | 100,000 | 200,000 | $800 |

## Optimization Strategies

### 1. Caching
Cache distance calculations for common locations:

```php
function getCachedDistance($userLat, $userLng, $centreId) {
    // Round to 2 decimal places for caching
    $latKey = round($userLat, 2);
    $lngKey = round($userLng, 2);
    $cacheKey = "dist_{$latKey}_{$lngKey}_{$centreId}";

    // Check cache (Redis, Memcached, or database)
    $cached = getFromCache($cacheKey);
    if ($cached !== null) {
        return $cached;
    }

    // Calculate and cache for 24 hours
    $distance = calculateDistanceViaAPI($userLat, $userLng, $centreId);
    setCache($cacheKey, $distance, 86400);

    return $distance;
}
```

### 2. Pre-filter with Haversine
Use Haversine to filter before calling Google API:

```php
// Quick filter: Get centres within bounding box first
$roughCentres = filterByHaversine($userLat, $userLng, $maxDistance + 10);

// Then get accurate distances only for rough matches
$accurateCentres = getGoogleDistances($userLat, $userLng, $roughCentres);
```

### 3. Batch Requests Efficiently
Group multiple user requests:

```php
// Queue user location requests
// Process in batches every 10 seconds
// Share API calls for similar locations
```

## Testing

### Test Endpoints

```bash
# Test nearby centres with backend
curl -X POST http://192.168.1.13/sn-progress-app/backend/api.php/nearby-centres \
  -H "Content-Type: application/json" \
  -d '{
    "latitude": 13.0827,
    "longitude": 80.2707,
    "max_distance": 25
  }'
```

Expected response:
```json
{
  "success": true,
  "centres": [
    {
      "id": 1,
      "name": "Anna Nagar, Chennai",
      "distance": 2.3,
      "distance_text": "2.3 km",
      "duration_text": "8 mins",
      ...
    }
  ],
  "count": 5
}
```

## Rollout Plan

### Phase 1: Development (Current)
- [x] Frontend Haversine calculation (completed)
- [ ] Test API key access
- [ ] Implement backend endpoint

### Phase 2: Testing
- [ ] Test with real user locations
- [ ] Compare frontend vs backend distances
- [ ] Test API quota limits
- [ ] Measure response times

### Phase 3: Deployment
- [ ] Deploy backend changes
- [ ] Monitor API usage
- [ ] Set up alerts for quota limits
- [ ] Implement fallback mechanism

### Phase 4: Optimization
- [ ] Implement caching
- [ ] Add pre-filtering
- [ ] Monitor costs
- [ ] Optimize batch processing

## Fallback Strategy

Always maintain frontend calculation as fallback:

```typescript
try {
  // Try backend with Google Maps
  await fetchNearbyCentresFromBackend(lat, lng)
} catch (error) {
  // Fallback to frontend Haversine
  console.warn('Using frontend distance calculation')
  filteredCenters.value = calculateDistancesLocally()
}
```

## Security Considerations

1. **API Key Protection**: Use environment variables, never commit to code
2. **Rate Limiting**: Implement backend rate limiting to prevent abuse
3. **Input Validation**: Validate latitude/longitude ranges
4. **Error Handling**: Don't expose API errors to frontend

## Conclusion

Google Maps backend integration will provide:
- ✅ More accurate distances
- ✅ Real road routes instead of straight-line
- ✅ Travel time estimates
- ✅ Better user experience

**Recommendation:** Implement Option 1 (Distance Matrix API) with caching and fallback to frontend calculation for cost optimization.
