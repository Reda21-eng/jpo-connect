// Ce fichier simule l'utilisateur connecté. À remplacer par une vraie gestion d'authentification !
export function getCurrentUser() {
  // Lecture depuis le localStorage (ou null si non connecté)
  const user = localStorage.getItem('user');
  return user ? JSON.parse(user) : null;
}

export function setCurrentUser(user) {
  if (user) {
    localStorage.setItem('user', JSON.stringify(user));
  } else {
    localStorage.removeItem('user');
  }
}
