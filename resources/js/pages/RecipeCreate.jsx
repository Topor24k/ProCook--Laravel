import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';
import {
    IoCreateOutline,
    IoAddCircleOutline,
    IoTrashOutline,
    IoArrowBackOutline,
    IoCheckmarkCircleOutline,
    IoRestaurantOutline,
    IoTimeOutline
} from 'react-icons/io5';

export default function RecipeCreate() {
    const { user } = useAuth();
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        title: '',
        short_description: '',
        cuisine_type: '',
        category: '',
        prep_time: '',
        cook_time: '',
        serving_size: '',
        preparation_notes: '',
        ingredients: [{ name: '', measurement: '', substitution_option: '', allergen_info: '' }]
    });
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    if (!user) {
        navigate('/login');
        return null;
    }

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            await api.post('/recipes', formData);
            navigate('/recipes/my-recipes');
        } catch (error) {
            setErrors(error.response?.data?.errors || {});
        } finally {
            setLoading(false);
        }
    };

    const addIngredient = () => {
        setFormData({
            ...formData,
            ingredients: [...formData.ingredients, { name: '', measurement: '', substitution_option: '', allergen_info: '' }]
        });
    };

    const removeIngredient = (index) => {
        const newIngredients = formData.ingredients.filter((_, i) => i !== index);
        setFormData({ ...formData, ingredients: newIngredients });
    };

    return (
        <div className="form-page" style={{ alignItems: 'flex-start', paddingTop: 'var(--space-2xl)' }}>
            <div className="form-container wide">
                <div style={{ textAlign: 'center', marginBottom: '2.5rem' }}>
                    <IoCreateOutline style={{ fontSize: '4rem', color: 'var(--primary)', marginBottom: '1rem' }} />
                    <h1 className="form-title">Share Your Recipe</h1>
                    <p className="form-subtitle">Create a new recipe and share it with the community</p>
                </div>
                
                <form onSubmit={handleSubmit}>
                    {/* Basic Information */}
                    <div className="form-group">
                        <label className="form-label">Recipe Title *</label>
                        <input
                            type="text"
                            className="form-input"
                            value={formData.title}
                            onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                            placeholder="e.g., Classic Italian Pasta Carbonara"
                            required
                        />
                        {errors.title && <div className="error-message">{errors.title[0]}</div>}
                    </div>

                    <div className="form-group">
                        <label className="form-label">Short Description *</label>
                        <textarea
                            className="form-textarea"
                            value={formData.short_description}
                            onChange={(e) => setFormData({ ...formData, short_description: e.target.value })}
                            placeholder="A brief description of your recipe..."
                            rows="3"
                            required
                        />
                    </div>

                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: 'var(--space-lg)' }}>
                        <div className="form-group">
                            <label className="form-label">Cuisine Type *</label>
                            <input
                                type="text"
                                className="form-input"
                                value={formData.cuisine_type}
                                onChange={(e) => setFormData({ ...formData, cuisine_type: e.target.value })}
                                placeholder="e.g., Italian"
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label className="form-label">Category *</label>
                            <input
                                type="text"
                                className="form-input"
                                value={formData.category}
                                onChange={(e) => setFormData({ ...formData, category: e.target.value })}
                                placeholder="e.g., Main Course"
                                required
                            />
                        </div>
                    </div>

                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: 'var(--space-lg)' }}>
                        <div className="form-group">
                            <label className="form-label">
                                <IoTimeOutline style={{ fontSize: '1.1rem', marginRight: '0.5rem', verticalAlign: 'middle' }} />
                                Prep Time (min) *
                            </label>
                            <input
                                type="number"
                                className="form-input"
                                value={formData.prep_time}
                                onChange={(e) => setFormData({ ...formData, prep_time: e.target.value })}
                                placeholder="15"
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label className="form-label">
                                <IoTimeOutline style={{ fontSize: '1.1rem', marginRight: '0.5rem', verticalAlign: 'middle' }} />
                                Cook Time (min) *
                            </label>
                            <input
                                type="number"
                                className="form-input"
                                value={formData.cook_time}
                                onChange={(e) => setFormData({ ...formData, cook_time: e.target.value })}
                                placeholder="30"
                                required
                            />
                        </div>
                        <div className="form-group">
                            <label className="form-label">
                                <IoRestaurantOutline style={{ fontSize: '1.1rem', marginRight: '0.5rem', verticalAlign: 'middle' }} />
                                Servings *
                            </label>
                            <input
                                type="number"
                                className="form-input"
                                value={formData.serving_size}
                                onChange={(e) => setFormData({ ...formData, serving_size: e.target.value })}
                                placeholder="4"
                                required
                            />
                        </div>
                    </div>

                    {/* Ingredients Section */}
                    <div style={{ 
                        marginTop: 'var(--space-2xl)', 
                        padding: 'var(--space-xl)', 
                        background: 'var(--gray-100)', 
                        borderRadius: 'var(--radius-md)' 
                    }}>
                        <h3 style={{ 
                            fontSize: '1.5rem', 
                            marginBottom: 'var(--space-lg)',
                            display: 'flex',
                            alignItems: 'center',
                            gap: 'var(--space-sm)'
                        }}>
                            <IoRestaurantOutline style={{ color: 'var(--primary)' }} />
                            Ingredients
                        </h3>
                        {formData.ingredients.map((ingredient, index) => (
                            <div key={index} style={{ 
                                background: 'var(--white)', 
                                padding: 'var(--space-lg)', 
                                borderRadius: 'var(--radius-md)', 
                                marginBottom: 'var(--space-md)',
                                border: '2px solid var(--gray-200)'
                            }}>
                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 'var(--space-md)' }}>
                                    <span style={{ fontWeight: '600', color: 'var(--gray-700)' }}>Ingredient {index + 1}</span>
                                    {formData.ingredients.length > 1 && (
                                        <button 
                                            type="button" 
                                            onClick={() => removeIngredient(index)}
                                            style={{
                                                background: 'transparent',
                                                border: 'none',
                                                color: 'var(--danger)',
                                                cursor: 'pointer',
                                                padding: '0.5rem',
                                                display: 'flex',
                                                alignItems: 'center',
                                                gap: '0.25rem',
                                                fontSize: '0.9rem'
                                            }}
                                        >
                                            <IoTrashOutline style={{ fontSize: '1.2rem' }} />
                                            Remove
                                        </button>
                                    )}
                                </div>
                                <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 'var(--space-md)' }}>
                                    <div className="form-group" style={{ marginBottom: 0 }}>
                                        <label className="form-label">Ingredient Name *</label>
                                        <input
                                            type="text"
                                            className="form-input"
                                            value={ingredient.name}
                                            onChange={(e) => {
                                                const newIngredients = [...formData.ingredients];
                                                newIngredients[index].name = e.target.value;
                                                setFormData({ ...formData, ingredients: newIngredients });
                                            }}
                                            placeholder="e.g., Olive Oil"
                                            required
                                        />
                                    </div>
                                    <div className="form-group" style={{ marginBottom: 0 }}>
                                        <label className="form-label">Measurement *</label>
                                        <input
                                            type="text"
                                            className="form-input"
                                            value={ingredient.measurement}
                                            onChange={(e) => {
                                                const newIngredients = [...formData.ingredients];
                                                newIngredients[index].measurement = e.target.value;
                                                setFormData({ ...formData, ingredients: newIngredients });
                                            }}
                                            placeholder="e.g., 2 tbsp"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>
                        ))}
                        <button 
                            type="button" 
                            onClick={addIngredient} 
                            className="btn-secondary"
                            style={{ width: '100%' }}
                        >
                            <IoAddCircleOutline style={{ fontSize: '1.3rem' }} />
                            Add Ingredient
                        </button>
                    </div>

                    {/* Preparation Instructions */}
                    <div className="form-group">
                        <label className="form-label">Preparation Instructions</label>
                        <textarea
                            className="form-textarea"
                            value={formData.preparation_notes}
                            onChange={(e) => setFormData({ ...formData, preparation_notes: e.target.value })}
                            rows="12"
                            placeholder="Step-by-step instructions for preparing this recipe..."
                            style={{ minHeight: '250px' }}
                        />
                    </div>

                    {/* Action Buttons */}
                    <div style={{ display: 'flex', gap: 'var(--space-md)', marginTop: 'var(--space-xl)' }}>
                        <button type="submit" className="form-button" disabled={loading} style={{ flex: 1 }}>
                            <IoCheckmarkCircleOutline style={{ fontSize: '1.3rem' }} />
                            {loading ? 'Publishing...' : 'Publish Recipe'}
                        </button>
                        <button 
                            type="button" 
                            onClick={() => navigate('/recipes')} 
                            className="btn-secondary"
                            style={{ flex: 1 }}
                        >
                            <IoArrowBackOutline style={{ fontSize: '1.2rem' }} />
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
