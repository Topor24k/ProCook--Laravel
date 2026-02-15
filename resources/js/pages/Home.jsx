import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import api from '../services/api';
import { useAuth } from '../context/AuthContext';
import {
    IoBookOutline,
    IoCreateOutline,
    IoHeartOutline,
    IoTimeOutline,
    IoRestaurantOutline,
    IoPersonOutline,
    IoArrowForwardOutline,
    IoSearchOutline,
    IoTrendingUpOutline,
    IoLockClosedOutline
} from 'react-icons/io5';

export default function Home() {
    const [recipes, setRecipes] = useState([]);
    const [loading, setLoading] = useState(true);
    const { isAuthenticated } = useAuth();

    useEffect(() => {
        fetchRecipes();
    }, []);

    const fetchRecipes = async () => {
        try {
            const response = await api.get('/recipes?limit=6');
            setRecipes(response.data);
        } catch (error) {
            console.error('Error fetching recipes:', error);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="home-page">
            {/* Hero Section */}
            <section className="hero-section">
                <div className="hero-content">
                    <h1 className="hero-title">
                        Discover Delicious<br />
                        <span className="gradient-text">Recipes</span>
                    </h1>
                    <p className="hero-subtitle">
                        Join our community of food lovers. Share your culinary creations
                        and discover amazing recipes from around the world.
                    </p>
                    <div className="hero-actions">
                        <Link to="/recipes" className="btn-hero-primary">
                            <IoSearchOutline style={{ fontSize: '1.3rem' }} />
                            Browse Recipes
                        </Link>
                        {isAuthenticated ? (
                            <Link to="/recipes/create" className="btn-hero-secondary">
                                <IoCreateOutline style={{ fontSize: '1.3rem' }} />
                                Create Recipe
                            </Link>
                        ) : (
                            <Link to="/register" className="btn-hero-secondary">
                                <IoPersonOutline style={{ fontSize: '1.3rem' }} />
                                Join Now
                            </Link>
                        )}
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section className="features-section">
                <div className="features-grid">
                    <div className="feature-card">
                        <div className="feature-icon">
                            <IoBookOutline style={{ fontSize: '4rem', color: 'var(--primary)' }} />
                        </div>
                        <h3>Browse Recipes</h3>
                        <p>Explore thousands of recipes from cuisines around the world. Find the perfect dish for any occasion.</p>
                    </div>
                    <div className="feature-card">
                        <div className="feature-icon">
                            <IoCreateOutline style={{ fontSize: '4rem', color: 'var(--accent)' }} />
                        </div>
                        <h3>Create & Share</h3>
                        <p>Share your favorite recipes with the community and inspire others with your culinary creativity.</p>
                    </div>
                    <div className="feature-card">
                        <div className="feature-icon">
                            <IoHeartOutline style={{ fontSize: '4rem', color: '#E74C3C' }} />
                        </div>
                        <h3>Save Favorites</h3>
                        <p>Keep track of recipes you love and want to cook again. Build your personal collection.</p>
                    </div>
                </div>
            </section>

            {/* Latest Recipes Section */}
            <section className="latest-recipes-section">
                <div className="section-header">
                    <h2 className="section-title">
                        <IoTrendingUpOutline style={{ fontSize: '2.5rem', marginRight: '0.5rem', verticalAlign: 'middle', color: 'var(--primary)' }} />
                        Latest Recipes
                    </h2>
                    <Link to="/recipes" className="view-all-link">
                        View All <IoArrowForwardOutline style={{ fontSize: '1.2rem' }} />
                    </Link>
                </div>

                {loading ? (
                    <div className="loading-state">
                        <div className="spinner"></div>
                        <p>Loading delicious recipes...</p>
                    </div>
                ) : recipes.length === 0 ? (
                    <div className="empty-state">
                        <div className="empty-state-icon">
                            <IoRestaurantOutline />
                        </div>
                        <h3 className="empty-state-title">No Recipes Yet</h3>
                        <p className="empty-state-description">
                            Be the first to share a recipe with our community!
                        </p>
                        {isAuthenticated && (
                            <Link to="/recipes/create" className="btn-primary">
                                <IoCreateOutline style={{ fontSize: '1.2rem' }} />
                                Create First Recipe
                            </Link>
                        )}
                    </div>
                ) : (
                    <div className="recipes-grid">
                        {recipes.map(recipe => (
                            <Link 
                                key={recipe.id} 
                                to={`/recipes/${recipe.id}`} 
                                className="recipe-card"
                            >
                                <div className="recipe-image">
                                    <div className="recipe-badge">{recipe.cuisine_type}</div>
                                    <div className="recipe-overlay">
                                        <span className="view-recipe">View Recipe</span>
                                    </div>
                                </div>
                                <div className="recipe-content">
                                    <h3 className="recipe-title">{recipe.title}</h3>
                                    <p className="recipe-description">
                                        {recipe.short_description?.substring(0, 100)}
                                        {recipe.short_description?.length > 100 && '...'}
                                    </p>
                                    <div className="recipe-meta">
                                        <span className="meta-item">
                                            <IoTimeOutline className="meta-icon" style={{ fontSize: '1.2rem' }} />
                                            {recipe.total_time} min
                                        </span>
                                        <span className="meta-item">
                                            <IoRestaurantOutline className="meta-icon" style={{ fontSize: '1.2rem' }} />
                                            {recipe.serving_size} servings
                                        </span>
                                    </div>
                                    <div className="recipe-footer">
                                        <span className="recipe-author">
                                            <IoPersonOutline style={{ fontSize: '1.1rem' }} />
                                            {recipe.user?.name || 'Anonymous'}
                                        </span>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </section>

            {/* CTA Section */}
            {!isAuthenticated && (
                <section className="cta-section">
                    <div className="cta-content">
                        <IoLockClosedOutline style={{ fontSize: '4rem', marginBottom: '1.5rem' }} />
                        <h2 className="cta-title">Ready to Start Cooking?</h2>
                        <p className="cta-description">
                            Join our community today and share your culinary masterpieces
                        </p>
                        <Link to="/register" className="btn-cta">
                            <IoPersonOutline style={{ fontSize: '1.3rem' }} />
                            Sign Up Free
                        </Link>
                    </div>
                </section>
            )}
        </div>
    );
}
