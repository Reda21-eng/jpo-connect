import React, { useState } from 'react';
import { getCurrentUser } from '../utils/auth';

function JPOCreationPage() {
  const [form, setForm] = useState({ title: '', description: '', date: '', capacity: 0 });
  const [message, setMessage] = useState('');
  const user = getCurrentUser();

  const handleChange = e => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async e => {
    e.preventDefault();
    const userData = { ...form, user_id: user.id };
    try {
      const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=create-jpo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData),
        credentials: 'include' // Ajout de cette ligne
      });
      const data = await res.json();
      if (!res.ok) {
        setMessage(data.error || 'Erreur lors de la création');
        return;
      }
      setMessage(data.success ? 'JPO créée !' : 'Erreur lors de la création');
    } catch (err) {
      setMessage('Erreur réseau ou réponse invalide du serveur');
    }
  };

  if (!user || user.role_id !== 1) {
    return <div className="card"><h2>Création JPO</h2><p style={{color:'red'}}>Accès réservé aux administrateurs.</p></div>;
  }

  return (
    <div className="card">
      <h2>Créer une Journée Portes Ouvertes</h2>
      <form onSubmit={handleSubmit}>
        <input name="title" placeholder="Titre" value={form.title} onChange={handleChange} required />
        <input name="description" placeholder="Description" value={form.description} onChange={handleChange} required />
        <input name="date" type="datetime-local" value={form.date} onChange={handleChange} required />
        <input name="capacity" type="number" placeholder="Capacité" value={form.capacity} onChange={handleChange} required />
        <button type="submit">Créer la JPO</button>
      </form>
      {message && <p>{message}</p>}
    </div>
  );
}

export default JPOCreationPage;
