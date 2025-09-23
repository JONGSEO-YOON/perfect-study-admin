// Firebase Cloud Messaging Service Worker

// Firebase SDK 불러오기
importScripts("https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js");
importScripts("https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js");

// Firebase 설정
const firebaseConfig = {
  apiKey: "AIzaSyBVxK6CCtCGABUWqCBt3DqAo_yF6bQ5m94", // TODO: 실제 API 키로 변경 필요
  authDomain: "perfact-study.firebaseapp.com",
  projectId: "perfact-study",
  storageBucket: "perfact-study.appspot.com",
  messagingSenderId: "847897044429", // TODO: 실제 Sender ID로 변경 필요
  appId: "1:847897044429:web:7b566a588c912882ba1084", // TODO: 실제 App ID로 변경 필요
};

// Firebase 초기화
firebase.initializeApp(firebaseConfig);

// Firebase Messaging 인스턴스 가져오기
const messaging = firebase.messaging();

// 전역 변수로 처리된 메시지 ID 저장 (중복 방지)
let processedMessages = new Set();

// 백그라운드 메시지 핸들러 (data-only 메시지 처리)
messaging.onBackgroundMessage((payload) => {
  // data-only 메시지에서 title, body 추출
  const title = payload.data?.title || "퍼펙트 스터디";
  const body = payload.data?.body || "새로운 알림이 있습니다.";
  const messageId = payload.data?.messageId || `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

  // 이미 처리된 메시지인지 확인
  if (processedMessages.has(messageId)) {
    return Promise.resolve();
  }

  // 처리된 메시지로 표시
  processedMessages.add(messageId);

  // 5분 후 메시지 ID 제거 (메모리 관리)
  setTimeout(() => {
    processedMessages.delete(messageId);
  }, 5 * 60 * 1000);

  return self.clients.matchAll({
    type: "window",
    includeUncontrolled: true
  }).then((clients) => {
    // 활성 창 상태 확인
    let hasVisibleApp = false;

    for (const client of clients) {
      if (client.url.includes("/parent")) {
        if (client.visibilityState === 'visible') {
          hasVisibleApp = true;

          // 포그라운드 앱에 메시지 전달
          client.postMessage({
            type: 'FCM_MESSAGE',
            payload: payload,
            title: title,
            body: body,
            messageId: messageId
          });
          return Promise.resolve(); // 포그라운드 처리 시 알림 표시 안함
        }
      }
    }

    // 포그라운드 앱이 없을 때만 알림 표시
    if (!hasVisibleApp) {
      const notificationOptions = {
        body: body,
        icon: "/icon-parent-192x192.png",
        badge: "/icon-parent-192x192.png",
        tag: "perfect-study-notification", // 고정 태그로 중복 방지
        data: {
          ...payload.data,
          messageId: messageId,
          timestamp: Date.now()
        },
        requireInteraction: true,
        renotify: true, // 새 알림으로 교체
        silent: false,
        actions: [
          {
            action: "open",
            title: "확인",
            icon: "/icon-parent-192x192.png",
          },
        ],
      };

      return self.registration.showNotification(title, notificationOptions);
    }

    return Promise.resolve();
  });
});

// 알림 클릭 이벤트 핸들러
self.addEventListener("notificationclick", (event) => {
  event.notification.close();

  if (event.action === "open" || !event.action) {
    // 알림 데이터에서 type 확인
    const notificationData = event.notification.data || {};
    const notificationType = notificationData.type;
    const messageId = notificationData.messageId;

    let targetUrl = "/parent"; // 기본 페이지

    // type에 따라 다른 페이지로 이동
    if (notificationType === "attendance") {
      targetUrl = "/parent/attendance"; // 출결 페이지
    } else if (notificationType === "payment") {
      targetUrl = "/parent/payment"; // 결제 페이지
    }

    // 알림 클릭 시 앱 열기
    event.waitUntil(
      self.clients.matchAll({
        type: "window",
        includeUncontrolled: true
      }).then((clientList) => {
        // 기존 앱 탭 찾기 (백그라운드 상태 포함)
        for (let i = 0; i < clientList.length; i++) {
          const client = clientList[i];

          if (client.url.includes("/parent")) {
            // 먼저 메시지를 보내고
            client.postMessage({
              action: "navigate",
              url: targetUrl,
              type: notificationType,
              messageId: messageId,
              fromNotification: true,
              timestamp: Date.now()
            });

            // 그 다음 포커스
            return client.focus().catch(() => {
              // 포커스 실패 시 새 창 열기
              if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
              }
            });
          }
        }

        // 앱이 열려있지 않으면 새 창 열기
        if (self.clients.openWindow) {
          return self.clients.openWindow(targetUrl);
        }
      }).catch(() => {
        // 오류 발생 시 기본 페이지 열기
        if (self.clients.openWindow) {
          return self.clients.openWindow("/parent");
        }
      })
    );
  }
});
