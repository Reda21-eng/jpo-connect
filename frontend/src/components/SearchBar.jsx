import React, { useState, useEffect } from 'react';

function SearchBar() {
  const [term, setTerm] = useState('');
  const [results, setResults] = useState([]);

  useEffect(() => {
    if (term.length > 1) {
      fetch(`http://localhost/jpo-connect/backend/index.php?path=search&term=${encodeURIComponent(term)}`)
        .then(res => res.json())
        .then(data => setResults(data.data || []));
    } else {
      setResults([]);
    }
  }, [term]);

  return (
    <div className="search-bar">
      <input
        type="text"
        placeholder="Rechercher une JPO..."
        value={term}
        onChange={e => setTerm(e.target.value)}
        autoComplete="on"
        name="search"
      />
      <ul>
        {results.map(s => (
          <li key={s.id}>{s.title} ({s.date})</li>
        ))}
      </ul>
    </div>
  );
}
export default SearchBar;
