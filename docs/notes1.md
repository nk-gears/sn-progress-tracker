  mysql -u mediuser -p meditation_tracker < database/event_register_table.sql


POST http://localhost:8080/api.php?action=bulk-centers
Content-Type: application/json

{
    "state": "Tamil Nadu",
    "district": "Tirunelveli",
    "locality": "Manipuram road (Near town arch), Tirunelveli",
    "address": "Manipuram road, Tirunelveli town, Near Town Arch",
    "contact_no": "9788272088",
    "address_contact_verified": "",
    "latitude_longitude": "TBC",
    "lat_long_verified": "",
    "url": "",
    "verified": ""
}