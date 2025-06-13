import React, { useEffect, useState } from 'react';

function NotificationBell() {
  const [notifications, setNotifications] = useState([]);
  useEffect(() => {
    // Remplacer 1 par l'ID étudiant courant si besoin
    fetch('http://localhost/jpo-connect/backend/index.php?path=notifications&student_id=1')
      .then(res => res.json())
      .then(data => setNotifications(data));
  }, []);
  return (
    <div className="notification-bell">
      <span role="img" aria-label="notifications">🔔</span>
      {notifications.length > 0 && <span className="notif-count">{notifications.length}</span>}
    </div>
  );
}
export default NotificationBell;
