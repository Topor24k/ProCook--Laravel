import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { 
    IoMailOutline, 
    IoLockClosedOutline, 
    IoLogInOutline,
    IoRestaurantOutline,
    IoAlertCircleOutline 
} from 'react-icons/io5';

export default function Login() {
    const { login } = useAuth();
    const navigate = useNavigate();
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            await login(email, password);
            navigate('/');
        } catch (err) {
            setError(err.response?.data?.message || 'Invalid credentials');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="form-page">
            <div className="form-container">
                <div style={{ textAlign: 'center', marginBottom: '2.5rem' }}>
                    <IoRestaurantOutline style={{ fontSize: '4rem', color: 'var(--primary)', marginBottom: '1rem' }} />
                    <h1 className="form-title">Welcome Back</h1>
                    <p className="form-subtitle">Please login to your account</p>
                </div>

                {error && (
                    <div style={{ 
                        background: '#FEE2E2', 
                        border: '1px solid #FCA5A5', 
                        padding: '1rem 1.25rem', 
                        borderRadius: 'var(--radius-md)', 
                        marginBottom: '1.5rem', 
                        color: 'var(--danger)', 
                        fontSize: '0.9rem',
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.75rem'
                    }}>
                        <IoAlertCircleOutline style={{ fontSize: '1.5rem', flexShrink: 0 }} />
                        <span>{error}</span>
                    </div>
                )}

                <form onSubmit={handleSubmit}>
                    <div className="form-group">
                        <label className="form-label">
                            <IoMailOutline style={{ fontSize: '1.1rem', marginRight: '0.5rem', verticalAlign: 'middle' }} />
                            Email Address
                        </label>
                        <input
                            type="email"
                            className="form-input"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            placeholder="your@email.com"
                            required
                        />
                    </div>

                    <div className="form-group">
                        <label className="form-label">
                            <IoLockClosedOutline style={{ fontSize: '1.1rem', marginRight: '0.5rem', verticalAlign: 'middle' }} />
                            Password
                        </label>
                        <input
                            type="password"
                            className="form-input"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            placeholder="Enter your password"
                            required
                        />
                    </div>

                    <button type="submit" className="form-button" disabled={loading}>
                        <IoLogInOutline style={{ fontSize: '1.3rem' }} />
                        {loading ? 'Logging in...' : 'Login'}
                    </button>
                </form>

                <div className="form-link">
                    <p>
                        Don't have an account? <Link to="/register">Sign up here</Link>
                    </p>
                </div>
            </div>
        </div>
    );
}
