import React, { useState } from 'react';

function RegistrationForm() {
  const [form, setForm] = useState({ firstname: '', lastname: '', email: '' });
  const [message, setMessage] = useState('');

  const handleChange = e => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async e => {
    e.preventDefault();
    const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form)
    });
    const data = await res.json();
    setMessage(data ? 'Inscription réussie !' : 'Erreur lors de l\'inscription');
  };

  return (
    <form className="card" onSubmit={handleSubmit} autoComplete="on">
      <h2>Inscription Étudiant</h2>
      <input name="firstname" placeholder="Prénom" value={form.firstname} onChange={handleChange} required autoComplete="given-name" />
      <input name="lastname" placeholder="Nom" value={form.lastname} onChange={handleChange} required autoComplete="family-name" />
      <input name="email" type="email" placeholder="Email" value={form.email} onChange={handleChange} required autoComplete="email" />
      <button type="submit">S'inscrire</button>
      {message && <p>{message}</p>}
    </form>
  );
}
export default RegistrationForm;
