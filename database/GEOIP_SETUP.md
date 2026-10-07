# GeoIP Database Setup

To enable GeoIP location tracking, you need to install the MaxMind GeoLite2 City database:

## Steps:

1. **Register for a free MaxMind account:**
   - Go to https://www.maxmind.com/en/geolite2/signup
   - Create a free account
   - Generate a license key

2. **Download the GeoLite2 City database:**
   - Download from: https://dev.maxmind.com/geoip/geolite2-free-geolocation-data
   - Or use the GeoIP update tool

3. **Install the database:**
   - Place the `GeoLite2-City.mmdb` file in: `database/GeoLite2-City.mmdb`
   - The system will automatically detect and use it

## Without GeoIP Database:

The system will work without the GeoIP database, but:
- Location data (country, city, coordinates) will be null
- Device and browser detection will still work
- Bot detection will still work
- Basic click tracking will function normally

## Alternative: Use Web Service

You can also configure the GeoIP2 web service instead of the local database
by modifying the GeoLocationService to use the web service API.