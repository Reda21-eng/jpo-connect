import React, { useState } from 'react';

function AdminRegistrationForm() {
  const [form, setForm] = useState({ username: '', email: '', password: '' });
  const [message, setMessage] = useState('');

  const handleChange = e => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async e => {
    e.preventDefault();
    const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=register-admin', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    });
    const data = await res.json();
    setMessage(data && data.success ? 'Compte administrateur créé !' : (data.error || 'Erreur lors de la création'));
  };

  return (
    <div className="card">
      <h2>Inscription Administrateur</h2>
      <form onSubmit={handleSubmit}>
        <input name="username" placeholder="Nom d'utilisateur" value={form.username} onChange={handleChange} required autoComplete="username" />
        <input name="email" type="email" placeholder="Email" value={form.email} onChange={handleChange} required autoComplete="email" />
        <input name="password" type="password" placeholder="Mot de passe" value={form.password} onChange={handleChange} required autoComplete="new-password" />
        <button type="submit">Créer un compte admin</button>
      </form>
      {message && <p>{message}</p>}
    </div>
  );
}
export default AdminRegistrationForm;
