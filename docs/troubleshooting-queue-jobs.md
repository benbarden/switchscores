# Troubleshooting Queue Jobs

## Symptoms

- Jobs stuck in `queued` status in `job_runs` table
- Supervisor errors mentioning missing vendor files, e.g.:
  ```
  ErrorException: include(...vendor/composer/../doctrine/inflector/...): Failed to open stream: No such file or directory
  ```
- Failed jobs UUID constraint errors:
  ```
  PDOException: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '' for key 'failed_jobs.failed_jobs_uuid_unique'
  ```

## Root Cause

The vendor directory is corrupted or incomplete, typically caused by:
- Deployment that didn't run `composer install`
- Failed `composer update` leaving inconsistent state
- Queue workers running during deployment with stale autoloader

## Fix Steps

### 1. Rebuild vendor directory

```bash
cd /var/www/switchscores.com
composer install --no-dev --optimize-autoloader
```

If that doesn't resolve it:

```bash
composer dump-autoload --optimize
```

### 2. Clear Laravel caches

```bash
php artisan cache:clear
php artisan config:clear
```

### 3. Clean up stuck jobs

Clear stuck queued jobs from `job_runs`:

```sql
DELETE FROM job_runs WHERE status = 'queued';
```

Clear Laravel's queue:

```bash
php artisan queue:clear
```

Clean corrupted failed_jobs entries (empty UUIDs):

```sql
DELETE FROM failed_jobs WHERE uuid = '';
```

### 4. Restart supervisor

```bash
sudo supervisorctl restart all
```

### 5. Verify

Trigger a test job from `/staff/tools` and check it completes:

```sql
SELECT id, command, status, exit_code, finished_at
FROM job_runs
ORDER BY id DESC
LIMIT 5;
```

A successful job will show `status = 'success'` and `exit_code = 0`.

## Prevention

Always run the post-deploy script after pulling new code:

```bash
cd /var/www/switchscores.com
./scripts/post-deploy.sh
```

This script handles composer install, cache clearing, migrations, and restarting queue workers in the correct order.
