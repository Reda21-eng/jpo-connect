import React, { useState } from 'react';

function RegisterForm({ onRegister }) {
  const [form, setForm] = useState({ username: '', email: '', password: '' });
  const [message, setMessage] = useState('');

  const handleChange = e => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async e => {
    e.preventDefault();
    const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=register-user', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    });
    const data = await res.json();
    if (data.success) {
      setMessage('Compte créé ! Vous pouvez vous connecter.');
      if (onRegister) onRegister();
    } else {
      setMessage(data.error || 'Erreur lors de la création du compte');
    }
  };

  return (
    <form className="card" onSubmit={handleSubmit} autoComplete="on">
      <h2>Créer un compte</h2>
      <input name="username" placeholder="Nom d'utilisateur" value={form.username} onChange={handleChange} required autoComplete="username" />
      <input name="email" type="email" placeholder="Email" value={form.email} onChange={handleChange} required autoComplete="email" />
      <input name="password" type="password" placeholder="Mot de passe" value={form.password} onChange={handleChange} required autoComplete="new-password" />
      <button type="submit">Créer le compte</button>
      {message && <p>{message}</p>}
    </form>
  );
}

export default RegisterForm;
