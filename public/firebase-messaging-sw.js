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

  // 중복 알림 방지: 앱이 포그라운드에 있으면 알림을 표시하지 않음
  return self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clients => {
    // 활성 창이 있으면 백그라운드 알림을 표시하지 않음
    if (clients.length > 0) {
      console.log("앱이 포그라운드에 있어 백그라운드 알림 표시 안함");
      return;
    }

    const notificationTitle = payload.notification.title || "퍼펙트 스터디";
    const notificationOptions = {
      body: payload.notification.body || "새로운 알림이 있습니다.",
      icon: "/icon-parent-192x192.png",
      badge: "/icon-parent-192x192.png",
      tag: "perfect-study-notification",
      data: payload.data,
      requireInteraction: true,
      renotify: true,
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
  });
});

// 알림 클릭 이벤트 핸들러
self.addEventListener("notificationclick", (event) => {
  console.log("알림 클릭됨: ", event);

  event.notification.close();

  if (event.action === "open" || !event.action) {
    // 알림 클릭 시 앱 열기
    event.waitUntil(
      clients.matchAll({ type: "window", includeUncontrolled: true }).then((clientList) => {
        // 이미 열려있는 창이 있으면 포커스
        for (let i = 0; i < clientList.length; i++) {
          const client = clientList[i];
          if (client.url.includes("/parent") && "focus" in client) {
            return client.focus();
          }
        }

        // 새 창 열기
        if (clients.openWindow) {
          return clients.openWindow("/parent");
        }
      })
    );
  }
});
