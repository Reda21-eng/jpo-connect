import React, { useEffect, useState } from 'react';
import { getCurrentUser } from '../utils/auth';
import './Dashboard.css';

function AdminDashboard({ quickRegOnly }) {
  // Formulaire d'inscription rapide toujours visible
  const [quickReg, setQuickReg] = useState({ firstname: '', lastname: '', email: '', jpo_id: '' });
  const [quickRegMsg, setQuickRegMsg] = useState('');
  const [jpos, setJpos] = useState([]);
  // Hooks pour le dashboard complet (déclarés une seule fois)
  const [form, setForm] = useState({ title: '', description: '', date: '', capacity: 0 });
  const [editingId, setEditingId] = useState(null);
  const [message, setMessage] = useState('');
  const [stats, setStats] = useState({});
  const [comments, setComments] = useState([]);
  const [siteContent, setSiteContent] = useState({ sessions: '', infos: '' });
  const [roles, setRoles] = useState([]);
  const [users, setUsers] = useState([]);
  const [modMsg, setModMsg] = useState('');
  const [contentMsg, setContentMsg] = useState('');

  useEffect(() => {
    fetch('http://localhost/jpo-connect/backend/index.php?path=jpos')
      .then(res => res.json()).then(data => setJpos(data.data || []));
    if (!quickRegOnly) {
      fetch('http://localhost/jpo-connect/backend/index.php?path=stats')
        .then(res => res.json()).then(data => setStats(data.data || {}));
      fetch('http://localhost/jpo-connect/backend/index.php?path=comments')
        .then(res => res.json()).then(data => setComments(data.data || []));
      fetch('http://localhost/jpo-connect/backend/index.php?path=site-content&type=sessions')
        .then(res => res.json()).then(data => setSiteContent(sc => ({ ...sc, sessions: data.data?.value || '' })));
      fetch('http://localhost/jpo-connect/backend/index.php?path=site-content&type=infos')
        .then(res => res.json()).then(data => setSiteContent(sc => ({ ...sc, infos: data.data?.value || '' })));
      fetch('http://localhost/jpo-connect/backend/index.php?path=roles')
        .then(res => res.json()).then(data => setRoles(data.data || []));
      fetch('http://localhost/jpo-connect/backend/index.php?path=users')
        .then(res => res.json()).then(data => setUsers(data.data || []));
    }
  }, [quickRegOnly]);

  const handleQuickReg = async (e) => {
    e.preventDefault();
    setQuickRegMsg('');
    if (!quickReg.firstname || !quickReg.lastname || !quickReg.email || !quickReg.jpo_id) {
      setQuickRegMsg('Tous les champs sont obligatoires.');
      return;
    }
    try {
      const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(quickReg)
      });
      const data = await res.json();
      if (data && data.success) {
        setQuickRegMsg('Inscription rapide réussie !');
        setQuickReg({ firstname: '', lastname: '', email: '', jpo_id: '' });
        // Rafraîchir les statistiques après inscription
        const statsRes = await fetch('http://localhost/jpo-connect/backend/index.php?path=stats');
        const statsData = await statsRes.json();
        setStats(statsData.data || {});
      } else {
        setQuickRegMsg(data.error || "Erreur lors de l'inscription rapide");
      }
    } catch (err) {
      setQuickRegMsg("Erreur de connexion au serveur");
    }
  };

  // Affichage du formulaire d'inscription rapide (toujours visible)
  const quickRegForm = (
    <section>
      <h3>Inscription rapide</h3>
      {quickRegMsg && <div className="success-msg">{quickRegMsg}</div>}
      <form onSubmit={handleQuickReg} autoComplete="on" style={{display: 'flex', flexWrap: 'wrap', gap: '0.5em', alignItems: 'center'}}>
        <input name="firstname" placeholder="Prénom" value={quickReg.firstname} onChange={e => setQuickReg({ ...quickReg, firstname: e.target.value })} required autoComplete="given-name" />
        <input name="lastname" placeholder="Nom" value={quickReg.lastname} onChange={e => setQuickReg({ ...quickReg, lastname: e.target.value })} required autoComplete="family-name" />
        <input name="email" type="email" placeholder="Email" value={quickReg.email} onChange={e => setQuickReg({ ...quickReg, email: e.target.value })} required autoComplete="email" />
        <select name="jpo_id" value={quickReg.jpo_id} onChange={e => setQuickReg({ ...quickReg, jpo_id: e.target.value })} required>
          <option value="">Choisir une JPO</option>
          {jpos.map(jpo => (
            <option key={jpo.id} value={jpo.id}>{jpo.title} - {jpo.date}</option>
          ))}
        </select>
        <button type="submit">S'inscrire</button>
      </form>
    </section>
  );

  if (quickRegOnly) {
    return <div className="admin-dashboard card">{quickRegForm}</div>;
  }

  const user = getCurrentUser();
  if (!user || user.role_id !== 1) {
    return (
      <div className="admin-dashboard card">
        {quickRegForm}
        <h2>Accès refusé</h2>
        <p>Seuls les administrateurs peuvent accéder à ce tableau de bord.</p>
      </div>
    );
  }

  // Permissions
  const canEditJPO = user && (user.role_id === 1);
  const canModerate = user && (user.role_id === 1);
  const canEditContent = user && (user.role_id === 1);
  const canManageRoles = user && (user.role_id === 1);

  // JPO CRUD
  const handleChange = e => setForm({ ...form, [e.target.name]: e.target.value });
  const handleEdit = (jpo) => {
    setForm({ title: jpo.title, description: jpo.description, date: jpo.date, capacity: jpo.capacity });
    setEditingId(jpo.id);
  };
  const handleDelete = async (id) => {
    if (!window.confirm('Supprimer cette JPO ?')) return;
    const res = await fetch(`http://localhost/jpo-connect/backend/index.php?path=jpos&id=${id}`, { method: 'DELETE', credentials: 'include' });
    if (res.ok) {
      setJpos(jpos.filter(jpo => jpo.id !== id));
      setMessage('JPO supprimée');
      await refreshStats();
    } else setMessage('Erreur suppression');
  };
  const handleSubmit = async e => {
    e.preventDefault();
    const url = editingId
      ? `http://localhost/jpo-connect/backend/index.php?path=jpos&id=${editingId}`
      : 'http://localhost/jpo-connect/backend/index.php?path=create-jpo';
    const res = await fetch(url, {
      method: editingId ? 'PUT' : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(form),
      credentials: 'include'
    });
    if (res.ok) {
      setMessage(editingId ? 'JPO modifiée' : 'JPO créée');
      setForm({ title: '', description: '', date: '', capacity: 0 });
      setEditingId(null);
      const jposRes = await fetch('http://localhost/jpo-connect/backend/index.php?path=jpos');
      const newData = await jposRes.json();
      setJpos(newData.data || []);
      await refreshStats();
    } else setMessage('Erreur serveur');
  };

  // Modération commentaires
  const handleModerate = async (id, status) => {
    const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=moderate-comment', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ comment_id: id, is_moderated: status }),
      credentials: 'include'
    });
    if (res.ok) {
      setModMsg('Commentaire modéré');
      await refreshStats(); // Rafraîchir stats après modération
    }
    else setModMsg('Erreur modération');
  };

  // Edition contenus
  const handleContentChange = async (type, value) => {
    const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=site-content', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type, content: value }),
      credentials: 'include'
    });
    if (res.ok) setContentMsg('Contenu modifié');
    else setContentMsg('Erreur modification contenu');
  };

  // Gestion rôles
  const handleRoleChange = async (userId, roleId) => {
    const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=users', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: userId, role: roleId }),
      credentials: 'include'
    });
    if (res.ok) {
      setMessage('Rôle modifié');
      await refreshStats(); // Rafraîchir stats après changement de rôle
    }
    else setMessage('Erreur modification rôle');
  };

  // Ajout notification (exemple, à adapter si tu as un handler)
  const handleAddNotification = async (notifData) => {
    const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=add-notification', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(notifData),
      credentials: 'include'
    });
    if (res.ok) {
      // ...
      await refreshStats(); // Rafraîchir stats après ajout notif
    }
  };

  // Suppression utilisateur (exemple, à adapter si tu as un handler)
  const handleDeleteUser = async (userId) => {
    const res = await fetch(`http://localhost/jpo-connect/backend/index.php?path=users&id=${userId}`, {
      method: 'DELETE',
      credentials: 'include'
    });
    if (res.ok) {
      // ...
      await refreshStats(); // Rafraîchir stats après suppression user
    }
  };

  // Marquer présence (exemple, à adapter si tu as un handler)
  const handleMarkPresence = async (studentId, present) => {
    const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=presence', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ student_id: studentId, present }),
      credentials: 'include'
    });
    if (res.ok) {
      // ...
      await refreshStats(); // Rafraîchir stats après présence
    }
  };

  const refreshStats = async () => {
    const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=stats');
    const data = await res.json();
    setStats(data.data || {});
  };

  return (
    <div className="admin-dashboard card">
      <h2>Tableau de bord équipe recrutement</h2>
      {message && <div className="success-msg">{message}</div>}
      <section>
        <h3>Statistiques</h3>
        <button onClick={async () => {
          const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=stats');
          const data = await res.json();
          setStats(data.data || {});
        }} style={{marginBottom: '1em'}}>Rafraîchir les statistiques</button>
        <ul>
          <li>Visiteurs inscrits : {stats.total_students}</li>
          <li>JPO : {stats.total_jpos}</li>
          <li>Utilisateurs : {stats.total_users}</li>
          <li>Commentaires : {stats.total_comments}</li>
          <li>Notifications : {stats.total_notifications}</li>
          <li>Présents : {stats.total_present || 0}</li>
        </ul>
      </section>
      <section>
        <h3>Gestion des JPO</h3>
        {canEditJPO && (
          <form onSubmit={handleSubmit} className="jpo-form" autoComplete="on">
            <input name="title" placeholder="Titre" value={form.title} onChange={handleChange} required autoComplete="off" />
            <input name="description" placeholder="Description" value={form.description} onChange={handleChange} required autoComplete="off" />
            <input name="date" type="datetime-local" value={form.date} onChange={handleChange} required autoComplete="off" />
            <input name="capacity" type="number" placeholder="Capacité" value={form.capacity} onChange={handleChange} required autoComplete="off" />
            <button type="submit">{editingId ? 'Modifier' : 'Créer'} la JPO</button>
            {editingId && (
              <button type="button" onClick={() => { setEditingId(null); setForm({ title: '', description: '', date: '', capacity: 0 }); }}>Annuler</button>
            )}
          </form>
        )}
        <ul>
          {jpos.map(jpo => (
            <li key={jpo.id}>
              <b>{jpo.title}</b> ({jpo.date}) - Capacité : {jpo.capacity} inscrits
              {canEditJPO && (
                <>
                  <button onClick={() => handleEdit(jpo)}>Modifier</button>
                  <button onClick={() => handleDelete(jpo.id)} style={{backgroundColor: '#ff4444'}}>Supprimer</button>
                </>
              )}
            </li>
          ))}
        </ul>
      </section>
      <section>
        <h3>Modération des commentaires</h3>
        {modMsg && <div className="success-msg">{modMsg}</div>}
        <ul>
          {comments.map(c => (
            <li key={c.id}>
              <b>{c.firstname} {c.lastname} :</b> {c.content}
              {canModerate && (
                <>
                  <button onClick={() => handleModerate(c.id, true)}>Valider</button>
                  <button onClick={() => handleModerate(c.id, false)}>Refuser</button>
                </>
              )}
            </li>
          ))}
        </ul>
      </section>
      <section>
        <h3>Contenus éditables</h3>
        {contentMsg && <div className="success-msg">{contentMsg}</div>}
        <div>
          <label>Sessions à venir :</label>
          <textarea value={siteContent.sessions} onChange={e => setSiteContent(sc => ({ ...sc, sessions: e.target.value }))} disabled={!canEditContent} />
          {canEditContent && <button onClick={() => handleContentChange('sessions', siteContent.sessions)}>Enregistrer</button>}
        </div>
        <div style={{marginTop: '1em'}}>
          <label>Infos pratiques :</label>
          <textarea value={siteContent.infos} onChange={e => setSiteContent(sc => ({ ...sc, infos: e.target.value }))} disabled={!canEditContent} />
          {canEditContent && <button onClick={() => handleContentChange('infos', siteContent.infos)}>Enregistrer</button>}
        </div>
      </section>
      <section>
        <h3>Gestion des utilisateurs et rôles</h3>
        <table>
          <thead>
            <tr>
              <th>Nom</th>
              <th>Email</th>
              <th>Rôle</th>
            </tr>
          </thead>
          <tbody>
            {users.map(u => (
              <tr key={u.id}>
                <td>{u.username}</td>
                <td>{u.email}</td>
                <td>
                  <select value={u.role_id} onChange={e => handleRoleChange(u.id, e.target.value)} disabled={!canManageRoles}>
                    {roles.map(r => (
                      <option key={r.id} value={r.id}>{r.name}</option>
                    ))}
                  </select>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </section>
      {/* Formulaire d'inscription rapide (si présent) */}
      {quickRegForm}
    </div>
  );
}

export default AdminDashboard;
