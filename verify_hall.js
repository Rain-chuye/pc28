const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.setViewportSize({ width: 375, height: 812 });
  await page.goto('file:///app/public/index.html');
  await page.screenshot({ path: '/home/jules/verification/game_hall_mobile.png' });
  await browser.close();
})();
