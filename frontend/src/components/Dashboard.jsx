import React, { useEffect, useState } from 'react';

function Dashboard() {
  const [students, setStudents] = useState([]);
  useEffect(() => {
    fetch('http://localhost/jpo-connect/backend/index.php?path=students')
      .then(res => res.json())
      .then(data => setStudents(data.data || []));
  }, []);

  const handleUnregister = async (id) => {
    await fetch(`http://localhost/jpo-connect/backend/index.php?path=unregister`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ student_id: id })
    });
    setStudents(students.filter(s => s.id !== id));
  };

  return (
    <div className="card">
      <h2>Tableau de bord</h2>
      <ul>
        {students.map(s => (
          <li key={s.id}>
            {s.firstname} {s.lastname} ({s.email})
            <button onClick={() => handleUnregister(s.id)} style={{marginLeft: '1em'}}>Se désinscrire</button>
          </li>
        ))}
      </ul>
    </div>
  );
}
export default Dashboard;
