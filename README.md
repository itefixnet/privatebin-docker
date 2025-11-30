# PrivateBin Docker - Apache

A Docker container running PrivateBin with Apache web server.

## Features

- **Apache 2.4** with PHP 8.2
- **PrivateBin** version 2.0.3
- Security headers configured
- Data persistence with Docker volumes
- Configuration and data mounted as volumes

## Quick Start

### Build and Run

```bash
# Build the image
docker build -t privatebin-apache .

# Run with Docker (with volume mounts)
docker run -d -p 8080:80 \
  -v ./data:/var/www/html/data \
  -v ./cfg:/var/www/html/cfg \
  --name privatebin privatebin-apache
```

Access PrivateBin at: `http://localhost:8080`

## Configuration

### Volume Mounts

Both configuration and data directories must be mounted as volumes:

```bash
# Create directories
mkdir -p data cfg

# Copy sample configuration
cp conf.php cfg/conf.php

# Set permissions
chmod 770 data
chmod 755 cfg
chmod 644 cfg/conf.php
```

Then mount them when running:

```bash
docker run -d -p 8080:80 \
  -v ./data:/var/www/html/data \
  -v ./cfg:/var/www/html/cfg \
  --name privatebin privatebin-apache
```

### Environment Variables

Customize the container by setting environment variables:

- `TZ`: Timezone (default: UTC)

Example:

```bash
docker run -d -p 8080:80 \
  -e TZ=America/New_York \
  -v ./data:/var/www/html/data \
  -v ./cfg:/var/www/html/cfg \
  --name privatebin privatebin-apache
```

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
docker build --build-arg PRIVATEBIN_VERSION=2.0.3 -t privatebin-apache .
```

## Docker Management

### Start the container

```bash
docker start privatebin
```

### Stop the container

```bash
docker stop privatebin
```

### View logs

```bash
docker logs -f privatebin
```

### Rebuild and restart

```bash
docker stop privatebin
docker rm privatebin
docker build -t privatebin-apache .
docker run -d -p 8080:80 \
  -v ./data:/var/www/html/data \
  -v ./cfg:/var/www/html/cfg \
  --name privatebin privatebin-apache
```

## Security Considerations

1. **HTTPS**: Use a reverse proxy (nginx, Traefik, Caddy) for HTTPS in production
2. **Data Directory**: Ensure proper permissions (770) and ownership
3. **Updates**: Regularly update to the latest PrivateBin version
4. **Configuration**: Review and customize `conf.php` for your security requirements

## Production Deployment

For production, use a reverse proxy with HTTPS:

```bash
# Run with restart policy and custom network
docker network create proxy

docker run -d \
  --name privatebin-apache \
  --restart unless-stopped \
  --network proxy \
  -e TZ=America/New_York \
  -v ./data:/var/www/html/data \
  -v ./cfg:/var/www/html/cfg \
  privatebin-apache
```

Then configure your reverse proxy (nginx, Traefik, Caddy, etc.) to handle HTTPS and forward to the container.

## Troubleshooting

### Permission Issues

```bash
# Fix data directory permissions
sudo chown -R 33:33 data/
chmod 770 data/
```

### Check Logs

```bash
docker logs privatebin
# or follow logs
docker logs -f privatebin
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
