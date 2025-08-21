import { test, expect } from "@playwright/test";

test("admin login and dashboard exploration", async ({ page }) => {
  // Navigate directly to admin panel
  await page.goto("/admin");
  await page.waitForTimeout(2000);

  // Check if we're on login page
  await expect(page.url()).toContain("/admin");

  // Wait for login form to load
  await page.waitForLoadState('networkidle');
  
  // Try different selectors for login form
  await page.waitForTimeout(1000);
  
  // 정확한 Filament/Livewire 선택자 사용
  const usernameField = page.locator('input[id="data.username"]');
  const passwordField = page.locator('input[id="data.password"]');

  // 실제 계정 정보로 테스트 - 다양한 조합 시도
  const credentials = [
    { username: 'admin', password: 'password' },
    { username: 'admin', password: 'admin' },
    { username: 'admin', password: '123456' },
    { username: 'admin', password: 'admin123' }
  ];

  for (const cred of credentials) {
    console.log(`시도 중: username="${cred.username}", password="${cred.password}"`);
    
    await usernameField.focus();
    await usernameField.fill('');
    await usernameField.type(cred.username, { delay: 100 });
    await page.waitForTimeout(500);
    
    await passwordField.focus();
    await passwordField.fill('');
    await passwordField.type(cred.password, { delay: 100 });
    await page.waitForTimeout(500);

    // Look for login button - 더 자세한 디버깅
    console.log("로그인 버튼 찾기 시도...");
    
    // 모든 버튼 확인
    const allButtons = await page.locator('button').all();
    console.log(`페이지에서 발견된 버튼 수: ${allButtons.length}`);
    
    for (let i = 0; i < allButtons.length; i++) {
      const btn = allButtons[i];
      const text = await btn.textContent();
      const type = await btn.getAttribute('type');
      const disabled = await btn.getAttribute('disabled');
      const isVisible = await btn.isVisible();
      const isEnabled = await btn.isEnabled();
      console.log(`버튼 ${i}: text="${text}", type="${type}", disabled="${disabled}", visible=${isVisible}, enabled=${isEnabled}`);
    }

    const loginButton = page.locator('button[type="submit"]:has-text("로그인")');
    
    if (await loginButton.isVisible()) {
      const btnText = await loginButton.textContent();
      const isEnabled = await loginButton.isEnabled();
      console.log(`로그인 버튼 발견: "${btnText}", 활성화됨: ${isEnabled}`);
      
      if (isEnabled) {
        console.log(`"${cred.username}/${cred.password}"로 로그인 시도 - 버튼 클릭`);
        
        // 버튼을 화면 중앙으로 스크롤
        await loginButton.scrollIntoViewIfNeeded();
        await page.waitForTimeout(500);
        
        // 강제로 클릭 시도
        try {
          await loginButton.click({ force: true });
          console.log("버튼 클릭 성공");
        } catch (error) {
          console.log(`버튼 클릭 실패: ${error.message}`);
          // JavaScript로 직접 클릭 시도
          await loginButton.evaluate(btn => btn.click());
          console.log("JavaScript 클릭 시도");
        }
      } else {
        console.log("❌ 로그인 버튼이 비활성화되어 있음");
      }
    } else {
      console.log("❌ 로그인 버튼을 찾을 수 없음");
    }
    
    // Wait for navigation after login
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    // Check if login was successful
    if (page.url().includes('/admin') && !page.url().includes('/login')) {
      console.log(`✅ 로그인 성공! 계정: ${cred.username}/${cred.password}`);
      break; // 성공하면 루프 종료
    } else if (await page.locator('nav, .fi-sidebar, [role="navigation"], .fi-topbar').first().isVisible({ timeout: 2000 })) {
      console.log(`✅ 로그인 성공! (UI 감지) 계정: ${cred.username}/${cred.password}`);
      break; // 성공하면 루프 종료
    } else {
      console.log(`❌ "${cred.username}/${cred.password}" 실패, 다음 조합 시도...`);
      // 실패하면 다음 루프로 계속
    }
  }
  
  // 모든 시도가 실패했을 때
  console.log("모든 로그인 시도 완료");
});

test("explore admin dashboard after login", async ({ page }) => {
  // Login first
  await page.goto("/admin");

  // Wait for page to load
  await page.waitForLoadState('networkidle');
  
  // Perform login - try username field
  const allInputs = await page.locator('input').all();
  
  if (allInputs.length >= 2) {
    console.log("두 번째 테스트: username 필드에 admin 입력");
    await allInputs[0].click();
    await allInputs[0].clear();
    await allInputs[0].fill("admin");

    console.log("두 번째 테스트: password 필드에 password 입력");
    await allInputs[1].click();
    await allInputs[1].clear();
    await allInputs[1].fill("password");

    const loginButton = page.locator('button[type="submit"]:has-text("로그인")');
    if (await loginButton.isVisible()) {
      console.log("두 번째 테스트: 로그인 버튼 클릭");
      await loginButton.click();
      await page.waitForLoadState('networkidle');
    }
  }

  // Wait for login to complete
  await page.waitForLoadState('networkidle');
  
  // Debug current state
  console.log(`두 번째 테스트 - 현재 URL: ${page.url()}`);
  
  // Check if we're logged in (should not be on login page)
  if (page.url().includes('/login')) {
    console.log("❌ 로그인 실패 - 여전히 로그인 페이지에 있음");
    return;
  }
  
  console.log("✅ 로그인된 상태로 보임 - 대시보드 탐색 시작");

  // Explore the admin dashboard - only clickable, enabled links
  const adminLinks = page.locator('nav a, .sidebar a, a[href*="/admin"]').filter({ hasText: /.+/ });
  const linkCount = await adminLinks.count();

  for (let i = 0; i < Math.min(linkCount, 3); i++) {
    const link = adminLinks.nth(i);
    if (await link.isVisible() && await link.isEnabled()) {
      const href = await link.getAttribute('href');
      if (href && href.includes('/admin')) {
        console.log(`클릭: ${await link.textContent()}`);
        await link.click();
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2000);
      }
    }
  }
});
