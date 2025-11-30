# PrivateBin Docker - Apache

A Docker container running PrivateBin with Apache web server.

## Features

- **Apache 2.4** with PHP 8.2
- **PrivateBin** latest stable version (1.7.4)
- Security headers configured
- Data persistence with Docker volumes
- Easy configuration through environment files
- Health checks included

## Quick Start

### Build and Run

```bash
# Build the image
docker build -t privatebin-apache .

# Run with Docker
docker run -d -p 8080:80 --name privatebin privatebin-apache

# Or use Docker Compose
docker-compose up -d
```

Access PrivateBin at: `http://localhost:8080`

## Configuration

### Custom PrivateBin Configuration

Edit `conf.php` to customize PrivateBin settings before building the image, or mount it as a volume:

```yaml
volumes:
  - ./conf.php:/var/www/html/cfg/conf.php:ro
```

### Data Persistence

Data is stored in the `data/` directory. Make sure it's writable:

```bash
mkdir -p data
chmod 770 data
```

### Environment Variables

Customize the container by setting environment variables in `docker-compose.yml`:

- `TZ`: Timezone (default: UTC)

### Apache Configuration

The Apache configuration in `apache-config.conf` includes:
- Security headers (CSP, X-Frame-Options, etc.)
- Access restrictions for data directory
- URL rewriting support

## Building

### Default Build

```bash
docker build -t privatebin-apache .
```

### Specify PrivateBin Version

```bash
docker build --build-arg PRIVATEBIN_VERSION=1.7.4 -t privatebin-apache .
```

## Docker Compose

The included `docker-compose.yml` provides a complete setup:

```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# View logs
docker-compose logs -f

# Rebuild
docker-compose up -d --build
```

## Security Considerations

1. **HTTPS**: Use a reverse proxy (nginx, Traefik, Caddy) for HTTPS in production
2. **Data Directory**: Ensure proper permissions (770) and ownership
3. **Updates**: Regularly update to the latest PrivateBin version
4. **Configuration**: Review and customize `conf.php` for your security requirements

## Production Deployment

For production, use a reverse proxy with HTTPS:

```yaml
version: '3.8'

services:
  privatebin:
    build: .
    container_name: privatebin-apache
    volumes:
      - ./data:/var/www/html/data
    environment:
      - TZ=America/New_York
    restart: unless-stopped
    networks:
      - proxy

networks:
  proxy:
    external: true
```

Then configure your reverse proxy (nginx, Traefik, etc.) to handle HTTPS.

## Troubleshooting

### Permission Issues

```bash
# Fix data directory permissions
sudo chown -R 33:33 data/
chmod 770 data/
```

### Check Logs

```bash
docker logs privatebin-apache
# or
docker-compose logs -f
```

### Verify Apache Configuration

```bash
docker exec privatebin-apache apache2ctl -t
```

## License

PrivateBin is licensed under the Zlib/libpng license. See the [PrivateBin repository](https://github.com/PrivateBin/PrivateBin) for details.

## Resources

- [PrivateBin Official Site](https://privatebin.info/)
- [PrivateBin GitHub](https://github.com/PrivateBin/PrivateBin)
- [PrivateBin Documentation](https://github.com/PrivateBin/PrivateBin/wiki)
