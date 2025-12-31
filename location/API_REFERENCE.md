# Centers API Reference

This document describes the JSON structure your backend API should return for the meditation centers finder application.

## API Endpoint

```
GET /api/centers
```

## Response Format

The API should return a JSON response with the following structure:

### Root Object

```json
{
  "success": true,
  "data": { ... },
  "metadata": { ... }
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `success` | boolean | Yes | Indicates if the request was successful |
| `data` | object | Yes | Contains the actual centers data |
| `metadata` | object | No | Additional information about the dataset |

### Data Object

```json
{
  "states": [...]
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `states` | array | Yes | Array of state objects |

### State Object

```json
{
  "id": 1,
  "name": "Tamil Nadu",
  "districts": [...]
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | number | Yes | Unique identifier for the state |
| `name` | string | Yes | State name (e.g., "Tamil Nadu", "South Kerala", "Puducherry") |
| `districts` | array | Yes | Array of district objects belonging to this state |

### District Object

```json
{
  "id": 1,
  "name": "Chennai",
  "state_id": 1,
  "centers": [...]
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | number | Yes | Unique identifier for the district |
| `name` | string | Yes | District name (e.g., "Chennai", "Coimbatore") |
| `state_id` | number | Yes | Foreign key referencing the parent state |
| `centers` | array | Yes | Array of center objects in this district (can be empty) |

### Center Object

```json
{
  "id": 1,
  "name": "Brahma Kumaris Raja Yoga Center - Chennai Central",
  "address": "123 Anna Salai, Mount Road, Chennai, Tamil Nadu 600002",
  "district_id": 1,
  "latitude": 13.0827,
  "longitude": 80.2707,
  "phone": "+91 44 1234 5678",
  "email": "chennai.central@brahmakumaris.com",
  "timings": "6:00 AM - 8:00 PM",
  "services": ["Meditation Classes", "Raja Yoga", "Spiritual Retreats"]
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `id` | number | Yes | Unique identifier for the center |
| `name` | string | Yes | Full name of the meditation center |
| `address` | string | Yes | Complete address including pincode |
| `district_id` | number | Yes | Foreign key referencing the parent district |
| `latitude` | number | Yes | Latitude coordinate for mapping |
| `longitude` | number | Yes | Longitude coordinate for mapping |
| `phone` | string | No | Contact phone number (with country code) |
| `email` | string | No | Contact email address |
| `timings` | string | No | Operating hours of the center |
| `services` | array | No | List of services offered at the center |

### Metadata Object (Optional)

```json
{
  "total_states": 3,
  "total_districts": 20,
  "total_centers": 16,
  "last_updated": "2025-12-31T18:30:00Z",
  "api_version": "1.0"
}
```

| Field | Type | Description |
|-------|------|-------------|
| `total_states` | number | Total number of states in the dataset |
| `total_districts` | number | Total number of districts across all states |
| `total_centers` | number | Total number of centers across all locations |
| `last_updated` | string (ISO 8601) | Timestamp of last data update |
| `api_version` | string | API version number |

## Error Response Format

In case of errors, the API should return:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable error message"
  }
}
```

## Example Responses

### Success Response (Full Data)

See `centers.json` for the complete example with all states, districts, and centers.

### Success Response (Empty District)

Districts without centers should have an empty `centers` array:

```json
{
  "id": 6,
  "name": "Tirunelveli",
  "state_id": 1,
  "centers": []
}
```

### Error Response Example

```json
{
  "success": false,
  "error": {
    "code": "DATABASE_ERROR",
    "message": "Unable to retrieve centers data. Please try again later."
  }
}
```

## Database Schema Reference

### Tables

#### states
```sql
CREATE TABLE states (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### districts
```sql
CREATE TABLE districts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    state_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (state_id) REFERENCES states(id)
);
```

#### centers
```sql
CREATE TABLE centers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    district_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    timings VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (district_id) REFERENCES districts(id)
);
```

#### center_services
```sql
CREATE TABLE center_services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    center_id INT NOT NULL,
    service_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
);
```

## PHP Backend Example

### API Endpoint Implementation

```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=meditation_centers", "username", "password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all states with their districts and centers
    $stmt = $pdo->query("
        SELECT
            s.id as state_id,
            s.name as state_name,
            d.id as district_id,
            d.name as district_name,
            c.id as center_id,
            c.name as center_name,
            c.address,
            c.latitude,
            c.longitude,
            c.phone,
            c.email,
            c.timings
        FROM states s
        LEFT JOIN districts d ON s.id = d.state_id
        LEFT JOIN centers c ON d.id = c.district_id
        ORDER BY s.name, d.name, c.name
    ");

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Transform flat result into hierarchical structure
    $states = [];
    $stateIndex = [];
    $districtIndex = [];

    foreach ($results as $row) {
        $stateId = $row['state_id'];
        $districtId = $row['district_id'];

        // Create state if not exists
        if (!isset($stateIndex[$stateId])) {
            $stateIndex[$stateId] = count($states);
            $states[] = [
                'id' => $stateId,
                'name' => $row['state_name'],
                'districts' => []
            ];
        }

        // Create district if not exists
        if ($districtId && !isset($districtIndex[$districtId])) {
            $districtIndex[$districtId] = count($states[$stateIndex[$stateId]]['districts']);
            $states[$stateIndex[$stateId]]['districts'][] = [
                'id' => $districtId,
                'name' => $row['district_name'],
                'state_id' => $stateId,
                'centers' => []
            ];
        }

        // Add center if exists
        if ($row['center_id']) {
            // Fetch services for this center
            $servicesStmt = $pdo->prepare("SELECT service_name FROM center_services WHERE center_id = ?");
            $servicesStmt->execute([$row['center_id']]);
            $services = $servicesStmt->fetchAll(PDO::FETCH_COLUMN);

            $states[$stateIndex[$stateId]]['districts'][$districtIndex[$districtId]]['centers'][] = [
                'id' => $row['center_id'],
                'name' => $row['center_name'],
                'address' => $row['address'],
                'district_id' => $districtId,
                'latitude' => floatval($row['latitude']),
                'longitude' => floatval($row['longitude']),
                'phone' => $row['phone'],
                'email' => $row['email'],
                'timings' => $row['timings'],
                'services' => $services
            ];
        }
    }

    // Count totals
    $totalCenters = $pdo->query("SELECT COUNT(*) FROM centers")->fetchColumn();
    $totalDistricts = $pdo->query("SELECT COUNT(*) FROM districts")->fetchColumn();

    // Build response
    $response = [
        'success' => true,
        'data' => [
            'states' => $states
        ],
        'metadata' => [
            'total_states' => count($states),
            'total_districts' => intval($totalDistricts),
            'total_centers' => intval($totalCenters),
            'last_updated' => date('c'),
            'api_version' => '1.0'
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'DATABASE_ERROR',
            'message' => 'Unable to retrieve centers data. Please try again later.'
        ]
    ]);
}
?>
```

## Frontend Integration

Update `app.js` to fetch from your API:

```javascript
async function fetchCentersFromAPI() {
    try {
        const response = await fetch('YOUR_API_URL/api/centers');
        const data = await response.json();

        if (data.success) {
            return data.data.states;
        } else {
            throw new Error(data.error.message);
        }
    } catch (error) {
        console.error('Error fetching centers:', error);
        throw error;
    }
}
```

## Notes

- All coordinates should use decimal degrees format (e.g., 13.0827, not DMS format)
- Phone numbers should include country code (e.g., +91 for India)
- Empty arrays are valid (for districts without centers)
- The `services` field can be null or an empty array if no services are defined
- Ensure proper UTF-8 encoding for international characters in names and addresses
