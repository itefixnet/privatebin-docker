# PrivateBin Docker

A secure Docker container running PrivateBin with Apache web server, featuring Prometheus metrics, data persistence, and optimized configuration for production deployments.

## Quick Start

```bash
# Pull and run
docker pull itefixnet/privatebin
docker run -d -p 8080:80 -v ./data:/srv/privatebin/data --name privatebin itefixnet/privatebin
```

Access at: `http://localhost:8080`

## Features

- **Apache 2.4** with PHP 8.2 (Debian-based)
- **PrivateBin** version 2.0.3
- **Prometheus metrics** endpoint at `/metrics`
- Optimized image size (~450-500MB)
- Security headers configured
- Data persistence with Docker volumes
- Production-ready configuration

## Usage

### Basic Run

```bash
docker run -d -p 8080:80 \
  -v ./data:/srv/privatebin/data \
  --name privatebin itefixnet/privatebin
```

### With Custom Configuration

```bash
docker run -d -p 8080:80 \
  -v ./data:/srv/privatebin/data \
  -v ./conf.php:/srv/privatebin/cfg/conf.php:ro \
  --name privatebin itefixnet/privatebin
```

### Production Deployment

```bash
docker run -d \
  --name privatebin \
  --restart unless-stopped \
  -p 8080:80 \
  -e TZ=America/New_York \
  -e METRICS_ALLOWED_IPS="10.0.1.5" \
  -v ./data:/srv/privatebin/data \
  -v ./conf.php:/srv/privatebin/cfg/conf.php:ro \
  itefixnet/privatebin
```

## Environment Variables

- `TZ`: Timezone (default: UTC)
- `METRICS_ALLOWED_IPS`: Space-separated list of IP addresses allowed to access `/metrics` endpoint

## Volumes

- `/srv/privatebin/data` - **Required**: Data storage directory
- `/srv/privatebin/cfg/conf.php` - Optional: Custom configuration file

## Prometheus Metrics

Access metrics at `http://localhost:8080/metrics`

**Available Metrics:**
- `privatebin_pastes_total` - Total number of pastes
- `privatebin_pastes_expired` - Number of expired pastes
- `privatebin_pastes_burn_after_reading` - Burn-after-reading pastes
- `privatebin_discussions_total` - Number of discussions
- `privatebin_storage_bytes` - Total storage used
- `privatebin_storage_files` - Total number of files
- Format statistics (plaintext, sourcecode, markdown)

### Prometheus Configuration

```yaml
scrape_configs:
  - job_name: 'privatebin'
    static_configs:
      - targets: ['privatebin-host:8080']
    metrics_path: '/metrics'
    scrape_interval: 30s
```

## Grafana Dashboard

A pre-built Grafana dashboard is available in the [GitHub repository](https://github.com/itefixnet/privatebin-docker). Import `grafana-dashboard.json` to visualize:
- Paste trends and statistics
- Storage usage and growth
- Format distribution
- Real-time metrics

## Security

**Important for Production:**
1. Use a reverse proxy (nginx, Traefik, Caddy) for HTTPS
2. Set proper permissions on data directory: `chmod 770 data/`
3. Regularly update to the latest version
4. Review and customize `conf.php` for your security requirements
5. Restrict metrics endpoint access with `METRICS_ALLOWED_IPS`

## Docker Compose Example

```yaml
version: '3.8'

services:
  privatebin:
    image: itefixnet/privatebin
    container_name: privatebin
    restart: unless-stopped
    ports:
      - "8080:80"
    environment:
      - TZ=America/New_York
      - METRICS_ALLOWED_IPS=10.0.1.5 192.168.1.10
    volumes:
      - ./data:/srv/privatebin/data
      - ./conf.php:/srv/privatebin/cfg/conf.php:ro
```

## Support & Documentation

- **GitHub Repository**: [itefixnet/privatebin-docker](https://github.com/itefixnet/privatebin-docker)
- **PrivateBin Official**: [privatebin.info](https://privatebin.info/)
- **PrivateBin GitHub**: [PrivateBin/PrivateBin](https://github.com/PrivateBin/PrivateBin)

## License

This Docker container is licensed under the BSD 2-Clause License.

PrivateBin itself is licensed under the Zlib/libpng license.

## Tags

- `latest` - Latest stable release
- `1.0.0` - Specific version tags

---

**Maintained by**: [Itefix Software](https://github.com/itefixnet)
