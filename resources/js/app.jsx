import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import Layout from './components/Layout';
import Home from './pages/Home';
import Recipes from './pages/Recipes';
import RecipeDetail from './pages/RecipeDetail';
import RecipeCreate from './pages/RecipeCreate';
import RecipeEdit from './pages/RecipeEdit';
import MyRecipes from './pages/MyRecipes';
import SavedRecipes from './pages/SavedRecipes';
import Login from './pages/Login';
import Register from './pages/Register';
import AboutUs from './pages/AboutUs';
import './index.css';

function App() {
    return (
        <AuthProvider>
            <BrowserRouter>
                <Routes>
                    <Route path="/" element={<Layout />}>
                        <Route index element={<Home />} />
                        
                        {/* Recipe Routes */}
                        <Route path="recipes" element={<Recipes />} />
                        <Route path="recipes/create" element={<RecipeCreate />} />
                        <Route path="recipes/my-recipes" element={<MyRecipes />} />
                        <Route path="recipes/saved" element={<SavedRecipes />} />
                        <Route path="recipes/:id" element={<RecipeDetail />} />
                        <Route path="recipes/:id/edit" element={<RecipeEdit />} />
                        
                        {/* Auth Routes */}
                        <Route path="login" element={<Login />} />
                        <Route path="register" element={<Register />} />
                        
                        {/* Info Routes */}
                        <Route path="about" element={<AboutUs />} />
                    </Route>
                </Routes>
            </BrowserRouter>
        </AuthProvider>
    );
}

ReactDOM.createRoot(document.getElementById('app')).render(
    <React.StrictMode>
        <App />
    </React.StrictMode>
);
