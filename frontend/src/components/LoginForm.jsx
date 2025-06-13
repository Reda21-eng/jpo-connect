import React, { useState } from 'react';

function LoginForm({ onLogin }) {
  const [form, setForm] = useState({ email: '', password: '' });
  const [message, setMessage] = useState('');

  const handleChange = e => setForm({ ...form, [e.target.name]: e.target.value });
  const handleSubmit = async e => {
    e.preventDefault();
    try {
      setMessage(''); // Réinitialiser le message
      const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
        credentials: 'include'
      });

      const data = await res.json();
      console.log('Réponse du serveur:', data); // Pour le débogage

      if (data.success && data.user) {
        setMessage('Connexion réussie !');
        if (onLogin) onLogin(data.user);
      } else {
        setMessage(data.error || 'Identifiants invalides');
      }
    } catch (error) {
      console.error('Erreur lors de la connexion:', error);
      setMessage('Erreur de connexion au serveur');
    }
  };

  return (
    <div className="card">
      <h2>Connexion</h2>
      <form onSubmit={handleSubmit}>
        <input name="email" type="email" placeholder="Email" value={form.email} onChange={handleChange} required autoComplete="email" />
        <input name="password" type="password" placeholder="Mot de passe" value={form.password} onChange={handleChange} required autoComplete="current-password" />
        <button type="submit">Se connecter</button>
      </form>
      {message && <p>{message}</p>}
    </div>
  );
}

export default LoginForm;
