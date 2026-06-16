# Canada 28 (PC28) Full-Stack Platform

## Environment Requirements
- **PHP**: 7.2 or higher
- **MySQL**: 5.6 or higher
- **Web Server**: Apache / Nginx

## Installation Steps
1. **Database Setup**:
   - Create a database (e.g., `pc28_db`).
   - Import the schema from `database/mysql_schema.sql`.

2. **Configuration**:
   - Edit `src/Config/database.php` and provide your MySQL credentials.

3. **Web Server Root**:
   - Point your web server's document root to the `public/` directory.

4. **Automation**:
   - Set up cron jobs for scraping results and settling bets:
     ```bash
     # Scrape results every 5 minutes
     */5 * * * * php /path/to/app/scripts/scraper.php
     # Settle bets every minute
     * * * * * php /path/to/app/scripts/settle.php
     ```

## Default Credentials
- **Admin Panel**: `/admin/index.php`
  - User: `admin`
  - Password: `admin123`
- **User Preview**:
  - User: `demo_user`
  - Password: `123456`

## Features
- **Premium Mobile UI**: Matching professional Canada 28 high-end platforms.
- **High/Standard Odds**: Configurable odds for all play types.
- **Admin Dashboard**: Manage users, adjust odds, and view betting history.
- **Auto-Settlement**: Automatic calculation and distribution of winnings.
