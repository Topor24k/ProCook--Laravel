import React from 'react';
import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { 
    IoRestaurantOutline, 
    IoHomeOutline, 
    IoBookOutline, 
    IoAddCircleOutline,
    IoLogOutOutline,
    IoLogInOutline,
    IoPersonAddOutline,
    IoPersonOutline,
    IoHeartOutline,
    IoMailOutline,
    IoLogoFacebook,
    IoLogoTwitter,
    IoLogoInstagram
} from 'react-icons/io5';

export default function Layout() {
    const { user, logout, isAuthenticated } = useAuth();
    const navigate = useNavigate();

    const handleLogout = async () => {
        await logout();
        navigate('/');
    };

    return (
        <div className="app-container">
            {/* Premium Header */}
            <header className="modern-header">
                <div className="header-content">
                    <Link to="/" className="logo">
                        <IoRestaurantOutline className="logo-icon" />
                        <span className="logo-text">ProCook</span>
                    </Link>

                    <nav className="main-nav">
                        <Link to="/" className="nav-link">
                            <IoHomeOutline style={{ fontSize: '1.2rem' }} /> Home
                        </Link>
                        <Link to="/recipes" className="nav-link">
                            <IoBookOutline style={{ fontSize: '1.2rem' }} /> Recipes
                        </Link>
                        {isAuthenticated && (
                            <Link to="/recipes/my-recipes" className="nav-link">
                                <IoHeartOutline style={{ fontSize: '1.2rem' }} /> My Recipes
                            </Link>
                        )}
                    </nav>

                    <div className="auth-actions">
                        {isAuthenticated ? (
                            <>
                                <Link to="/recipes/create" className="btn-primary">
                                    <IoAddCircleOutline style={{ fontSize: '1.2rem' }} />
                                    <span>Create Recipe</span>
                                </Link>
                                <div className="user-menu">
                                    <span className="user-name">
                                        <IoPersonOutline style={{ fontSize: '1.1rem', marginRight: '0.25rem' }} />
                                        {user?.name}
                                    </span>
                                    <button onClick={handleLogout} className="btn-secondary">
                                        <IoLogOutOutline style={{ fontSize: '1.1rem' }} />
                                        Logout
                                    </button>
                                </div>
                            </>
                        ) : (
                            <>
                                <Link to="/login" className="btn-secondary">
                                    <IoLogInOutline style={{ fontSize: '1.1rem' }} />
                                    Login
                                </Link>
                                <Link to="/register" className="btn-primary">
                                    <IoPersonAddOutline style={{ fontSize: '1.1rem' }} />
                                    Sign Up
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="main-content">
                <Outlet />
            </main>

            {/* Premium Footer */}
            <footer className="modern-footer">
                <div className="footer-content">
                    <div className="footer-section">
                        <h3>
                            <IoRestaurantOutline style={{ fontSize: '1.8rem', marginBottom: '0.5rem' }} />
                            ProCook
                        </h3>
                        <p>Discover, create, and share amazing recipes with food lovers worldwide. Join our community of passionate cooks and culinary enthusiasts.</p>
                        <div style={{ display: 'flex', gap: '1rem', marginTop: '1.5rem' }}>
                            <a href="#" style={{ fontSize: '1.5rem', display: 'inline' }}>
                                <IoLogoFacebook />
                            </a>
                            <a href="#" style={{ fontSize: '1.5rem', display: 'inline' }}>
                                <IoLogoTwitter />
                            </a>
                            <a href="#" style={{ fontSize: '1.5rem', display: 'inline' }}>
                                <IoLogoInstagram />
                            </a>
                        </div>
                    </div>
                    <div className="footer-section">
                        <h4>Quick Links</h4>
                        <Link to="/recipes">Browse Recipes</Link>
                        <Link to="/recipes/create">Create Recipe</Link>
                        {isAuthenticated && <Link to="/recipes/my-recipes">My Recipes</Link>}
                        <a href="#">Recipe Collections</a>
                        <a href="#">Popular</a>
                    </div>
                    <div className="footer-section">
                        <h4>Community</h4>
                        <a href="#">About Us</a>
                        <a href="#">Contact</a>
                        <a href="#">Terms of Service</a>
                        <a href="#">Privacy Policy</a>
                        <a href="#">Help Center</a>
                    </div>
                    <div className="footer-section">
                        <h4>Newsletter</h4>
                        <p style={{ fontSize: '0.9rem', marginBottom: '1rem' }}>
                            Stay updated with the latest recipes and culinary tips!
                        </p>
                        <div style={{ display: 'flex', gap: '0.5rem' }}>
                            <input 
                                type="email" 
                                placeholder="Your email" 
                                style={{
                                    flex: 1,
                                    padding: '0.625rem 0.875rem',
                                    borderRadius: '6px',
                                    border: '1px solid var(--gray-700)',
                                    background: 'var(--gray-800)',
                                    color: 'var(--gray-300)',
                                    fontSize: '0.875rem'
                                }}
                            />
                            <button style={{
                                padding: '0.625rem 1rem',
                                borderRadius: '6px',
                                border: 'none',
                                background: 'var(--primary)',
                                color: 'white',
                                cursor: 'pointer',
                                fontWeight: '600'
                            }}>
                                <IoMailOutline style={{ fontSize: '1.2rem' }} />
                            </button>
                        </div>
                    </div>
                </div>
                <div className="footer-bottom">
                    <p>&copy; 2026 ProCook. All rights reserved. Made with <IoHeartOutline style={{ display: 'inline', verticalAlign: 'middle', color: 'var(--primary)' }} /> for food lovers.</p>
                </div>
            </footer>
        </div>
    );
}
