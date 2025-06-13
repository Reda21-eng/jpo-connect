import React, { useState } from 'react';
import RegisterForm from './components/RegisterForm.jsx';
import LoginForm from './components/LoginForm.jsx';
import RegistrationForm from './components/RegistrationForm.jsx';
import Dashboard from './components/Dashboard.jsx';
import CommentsSection from './components/CommentsSection.jsx';
import AdminDashboard from './components/AdminDashboard.jsx';
import NotificationBell from './components/NotificationBell.jsx';
import SearchBar from './components/SearchBar.jsx';
import './App.css';
import { getCurrentUser, setCurrentUser } from './utils/auth';

function App() {
  const [user, setUser] = useState(getCurrentUser());
  const handleLogin = (userData) => {
    setUser(userData);
    setCurrentUser(userData);
  };
  const handleRegister = () => {
    // Optionnel : afficher un message ou basculer sur le formulaire de connexion
  };
  const handleLogout = () => {
    setUser(null);
    setCurrentUser(null);
  };
  return (
    <div className="container">
      <header>
        <h1 style={{margin: 0, fontSize: '2.8em', fontWeight: 'bold', lineHeight: 1}}>
          La Plateforme
        </h1>
        <div style={{fontSize: '1.1em', color: '#00b4d8', fontWeight: 400, letterSpacing: '0.5px', marginTop: '-0.5em'}}>
          Grande École du Numérique
        </div>
        <div style={{fontSize: '1.3em', color: '#00b4d8', fontWeight: 400, marginBottom: '0.5em'}}>
          Journée Portes Ouvertes
        </div>
        <NotificationBell />
        <SearchBar />
        {user && <button onClick={handleLogout}>Se déconnecter</button>}
      </header>
      <main>
        {/* Formulaire d'inscription rapide toujours visible */}
        <AdminDashboard quickRegOnly />
        {!user ? (
          <>
            <LoginForm onLogin={handleLogin} />
            <RegisterForm onRegister={handleRegister} />
            {/* Le dashboard complet n'est PAS affiché ici */}
          </>
        ) : (
          <>
            <RegistrationForm />
            <Dashboard />
            <CommentsSection />
            {/* Dashboard complet seulement pour les admins */}
            {user.role_id === 1 && <AdminDashboard />}
          </>
        )}
      </main>
    </div>
  );
}

export default App;
