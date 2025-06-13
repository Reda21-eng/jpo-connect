import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import App from './App.jsx';
import AdminDashboard from './components/AdminDashboard.jsx';
import AdminRegistrationForm from './components/AdminRegistrationForm.jsx';
import RegisterForm from './components/RegisterForm.jsx';
import LoginForm from './components/LoginForm.jsx';
import RegistrationForm from './components/RegistrationForm.jsx';
import Dashboard from './components/Dashboard.jsx';
import CommentsSection from './components/CommentsSection.jsx';

function AppRouter() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<App />} />
        <Route path="/admin" element={<AdminDashboard />} />
        <Route path="/admin/register" element={<AdminRegistrationForm />} />
        <Route path="/register" element={<RegisterForm />} />
        <Route path="/login" element={<LoginForm />} />
        <Route path="/inscription" element={<RegistrationForm />} />
        <Route path="/dashboard" element={<Dashboard />} />
        <Route path="/commentaires" element={<CommentsSection />} />
      </Routes>
    </Router>
  );
}

export default AppRouter;
