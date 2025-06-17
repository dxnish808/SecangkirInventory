# Docker Setup for SecangkirInventory

This guide will help you set up and run the SecangkirInventory application using Docker, replacing the need for XAMPP.

## Prerequisites

- Docker installed on your system
- Docker Compose installed on your system

## Services Included

1. **Web Application** (PHP 8.2 + Apache) - Port 8090
2. **MySQL Database** (MySQL 8.0) - Port 3306
3. **phpMyAdmin** (Database Management) - Port 8091

## Quick Start

1. **Clone/Navigate to the project directory:**
   ```bash
   cd /path/to/SecangkirInventory
   ```

2. **Build and start all services:**
   ```bash
   docker-compose up -d --build
   ```

3. **Access the application:**
   - **Main Application**: http://localhost:8090
   - **phpMyAdmin**: http://localhost:8091
   - **Database**: localhost:3306

## Default Login Credentials

### Application Login
- **Admin**: Username: `Nas`, Password: `hello` (SHA1: 40bd001563085fc35165329ea1ff5c5ecbdbbeef)
- **Employee**: Username: `Danish`, Password: `hello`

### Database Access
- **Root Password**: `rootpassword`
- **Database Name**: `inventory_system`
- **Application User**: `inventory_user`
- **Application Password**: `inventory_pass`

## Common Commands

### Start the application
```bash
docker-compose up -d
```

### Stop the application
```bash
docker-compose down
```

### View logs
```bash
# All services
docker-compose logs

# Specific service
docker-compose logs web
docker-compose logs db
docker-compose logs phpmyadmin
```

### Rebuild after changes
```bash
docker-compose down
docker-compose up -d --build
```

### Access database directly
```bash
docker exec -it secangkir_db mysql -u root -p
```

## Database Information

- The database is automatically initialized with the existing `inventory_system.sql` file
- Data is persisted in a Docker volume named `mysql_data`
- The database will be available even after container restarts

## File Structure

```
SecangkirInventory/
├── Dockerfile                 # PHP application container
├── docker-compose.yml         # Services orchestration
├── .dockerignore             # Files to exclude from build
├── DATABASE FILE/
│   └── inventory_system.sql  # Database schema and data
├── includes/
│   └── config.php           # Updated database configuration
└── ... (rest of PHP files)
```

## Troubleshooting

### Port Conflicts
If ports 8080, 8081, or 3306 are already in use, you can change them in `docker-compose.yml`:

```yaml
ports:
  - "9080:80"  # Change 8080 to 9080
```

### Database Connection Issues
- Ensure the database container is running: `docker-compose ps`
- Check database logs: `docker-compose logs db`
- Verify environment variables in `docker-compose.yml`

### File Permissions
If you encounter permission issues with uploads:
```bash
docker exec -it secangkir_web chown -R www-data:www-data /var/www/html/uploads
```

### Reset Database
To reset the database to initial state:
```bash
docker-compose down
docker volume rm secangkirinventory_mysql_data
docker-compose up -d
```

## Development

For development, the current directory is mounted as a volume, so changes to PHP files will be reflected immediately without rebuilding the container.

## Production Considerations

For production deployment:
1. Change default passwords in `docker-compose.yml`
2. Use environment variables for sensitive data
3. Configure proper SSL/TLS
4. Set up proper backup strategies for the database volume
5. Consider using Docker secrets for sensitive information

## Stopping XAMPP

Since you're replacing XAMPP:
1. Stop Apache and MySQL services in XAMPP
2. The Docker setup uses different ports (8090 instead of 80) to avoid conflicts
3. You can run both simultaneously if needed for migration

## Support

If you encounter any issues:
1. Check the logs using `docker-compose logs`
2. Ensure Docker and Docker Compose are properly installed
3. Verify that the required ports are available
4. Check file permissions in the project directory 