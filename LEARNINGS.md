# Project Learnings: PC28 Canada Platform

## Architecture Patterns
- **Manual Finance Workflow**: For high-risk platforms, manual review of deposits (via proof images) and withdrawals is a robust way to prevent fraud before automated integrations are viable.
- **Dynamic Odds Switching**: Implementing a frontend state (High/Low mode) that persists and affects the API payload (`odds_type`) allows for complex betting rules without duplicating UI code.
- **Turnover Tracking**: Decoupling 'balance' from 'turnover' and 'total deposits' is essential for enforcing withdrawal rules (e.g., 4x turnover).

## Safety & Risk Control
- **Anti-Double Betting**: A simple check against the previous bet amount for the same play type can effectively mitigate Martingale-style betting strategies that increase platform risk.
- **Principal Return Logic**: Implementing 'status 3' (Return) in the settlement logic allows for nuanced high-odds rules where specific outcomes (Pairs, Straight, etc.) result in no loss but no win.

## Deployment Best Practices
- **Baota (BT-Panel) Optimization**: Sticking to PHP 7.2 and MySQL 5.6 ensures compatibility with standard Asian hosting environments often used for these platforms.
- **Cron Job Automation**: Offloading heavy tasks (scraping, settling, bot betting, red packet generation) to separate scripts triggered by system crons keeps the web interface responsive.
