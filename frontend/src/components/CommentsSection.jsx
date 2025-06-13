import React, { useEffect, useState } from 'react';
import { getCurrentUser } from '../utils/auth';

function CommentsSection() {
  const [comments, setComments] = useState([]);
  const [content, setContent] = useState('');
  const [message, setMessage] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const user = getCurrentUser();

  const fetchComments = async () => {
    try {
      setIsLoading(true);
      const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=comments', {
        credentials: 'include'
      });
      const data = await res.json();
      console.log('Réponse des commentaires:', data); // Pour le débogage
      
      if (data.success) {
        setComments(data.data || []);
      } else {
        setMessage('Erreur lors du chargement des commentaires');
        console.error('Erreur:', data.error);
      }
    } catch (error) {
      setMessage('Erreur lors du chargement des commentaires');
      console.error('Erreur:', error);
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    fetchComments();
  }, []);

  const handleSubmit = async e => {
    e.preventDefault();
    
    if (!content.trim()) {
      setMessage('Le commentaire ne peut pas être vide');
      return;
    }

    try {
      const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=add-comment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content: content.trim() }),
        credentials: 'include'
      });

      const data = await res.json();
      
      if (data.success) {
        setContent('');
        setMessage('Commentaire ajouté et en attente de modération');
        fetchComments(); // Actualiser la liste des commentaires
      } else {
        setMessage(data.error || 'Erreur lors de l\'ajout du commentaire');
      }
    } catch (err) {
      setMessage('Erreur de connexion au serveur');
      console.error('Erreur:', err);
    }
  };

  const handleModerate = async (id, status) => {
    try {
      const res = await fetch('http://localhost/jpo-connect/backend/index.php?path=moderate-comment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status }),
        credentials: 'include'
      });

      const data = await res.json();
      if (data.success) {
        setMessage(`Commentaire ${status === 'approved' ? 'approuvé' : 'rejeté'}`);
        fetchComments();
      } else {
        setMessage(data.error || 'Erreur lors de la modération');
      }
    } catch (error) {
      setMessage('Erreur réseau');
      console.error('Erreur:', error);
    }
  };

  if (isLoading) {
    return <div className="card">
      <h3>Commentaires</h3>
      <p>Chargement des commentaires...</p>
    </div>;
  }

  return (
    <div className="card">
      <h3>Commentaires</h3>
      <form onSubmit={handleSubmit}>
        <input 
          value={content} 
          onChange={e => setContent(e.target.value)} 
          placeholder="Votre commentaire..." 
          required 
        />
        <button type="submit">Envoyer</button>
      </form>
      {message && <p>{message}</p>}
      <ul>
        {comments.length === 0 ? (
          <p>Aucun commentaire pour le moment.</p>
        ) : (
          comments.map(c => (
            <li key={c.id} className={`comment-${c.status || 'pending'}`}>
              <div className="comment-content">{c.content}</div>
              <div className="comment-meta">
                {c.created_at && (
                  <span className="comment-date">
                    {new Date(c.created_at).toLocaleDateString()}
                  </span>
                )}
                {c.moderator_name && (
                  <span className="comment-moderator">
                    Modéré par: {c.moderator_name}
                  </span>
                )}
              </div>
              {user && user.role_id === 1 && c.status === 'pending' && (
                <div className="moderation-buttons">
                  <button 
                    onClick={() => handleModerate(c.id, 'approved')}
                    className="approve-btn"
                  >
                    Approuver
                  </button>
                  <button 
                    onClick={() => handleModerate(c.id, 'rejected')}
                    className="reject-btn"
                  >
                    Rejeter
                  </button>
                </div>
              )}
            </li>
          ))
        )}
      </ul>
    </div>
  );
}

export default CommentsSection;
