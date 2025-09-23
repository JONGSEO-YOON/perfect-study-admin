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

// 백그라운드 메시지 핸들러
messaging.onBackgroundMessage((payload) => {
  console.log("=== FCM 백그라운드 메시지 받음 ===");
  console.log("Payload:", payload);
  console.log("Notification:", payload.notification);
  console.log("Data:", payload.data);

  // 중복 알림 방지를 위한 고유 ID 생성
  const messageId = payload.data?.messageId || `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  console.log("Message ID:", messageId);

  return self.clients.matchAll({ type: "window", includeUncontrolled: true }).then((clients) => {
    // 활성 창 상태 확인
    let hasVisibleApp = false;

    for (const client of clients) {
      if (client.url.includes("/parent") && client.visibilityState === 'visible') {
        hasVisibleApp = true;
        console.log("앱이 포그라운드에 있음 - 포그라운드 처리로 위임");

        // 포그라운드 앱에 메시지 전달
        client.postMessage({
          type: 'FCM_MESSAGE',
          payload: payload,
          messageId: messageId
        });
        break;
      }
    }

    // 포그라운드에 앱이 없거나 백그라운드 상태일 때만 알림 표시
    if (!hasVisibleApp) {
      const notificationTitle = payload.notification?.title || "퍼펙트 스터디";
      const notificationOptions = {
        body: payload.notification?.body || "새로운 알림이 있습니다.",
        icon: "/icon-parent-192x192.png",
        badge: "/icon-parent-192x192.png",
        tag: messageId, // 고유 ID로 중복 방지
        data: {
          ...payload.data,
          messageId: messageId,
          timestamp: Date.now()
        },
        requireInteraction: true,
        renotify: false, // 중복 방지를 위해 false로 변경
        actions: [
          {
            action: "open",
            title: "확인",
            icon: "/icon-parent-192x192.png",
          },
        ],
      };

      console.log("백그라운드 알림 표시:", notificationTitle);
      return self.registration.showNotification(notificationTitle, notificationOptions);
    }
  });
});

// 알림 클릭 이벤트 핸들러
self.addEventListener("notificationclick", (event) => {
  console.log("=== 알림 클릭됨 ===");
  console.log("Event:", event);
  console.log("알림 데이터:", event.notification.data);

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

    console.log("알림 타입:", notificationType);
    console.log("메시지 ID:", messageId);
    console.log("이동할 URL:", targetUrl);

    // 알림 클릭 시 앱 열기
    event.waitUntil(
      self.clients.matchAll({
        type: "window",
        includeUncontrolled: true
      }).then((clientList) => {
        console.log("찾은 클라이언트 수:", clientList.length);

        // 기존 앱 탭 찾기 (백그라운드 상태 포함)
        for (let i = 0; i < clientList.length; i++) {
          const client = clientList[i];
          console.log(`클라이언트 ${i}:`, client.url, "상태:", client.visibilityState);

          if (client.url.includes("/parent")) {
            console.log("기존 앱 탭 발견 - 포커스 및 네비게이션");

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
            return client.focus().then(() => {
              console.log("앱 포커스 완료");
              return client;
            }).catch((error) => {
              console.error("포커스 실패:", error);
              // 포커스 실패 시 새 창 열기
              if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
              }
            });
          }
        }

        // 앱이 열려있지 않으면 새 창 열기
        console.log("기존 앱 탭 없음 - 새 창 열기");
        if (self.clients.openWindow) {
          return self.clients.openWindow(targetUrl);
        }
      }).catch((error) => {
        console.error("알림 클릭 처리 오류:", error);
        // 오류 발생 시 기본 페이지 열기
        if (self.clients.openWindow) {
          return self.clients.openWindow("/parent");
        }
      })
    );
  }
});
