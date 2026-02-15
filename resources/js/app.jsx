import React from 'react';
import ReactDOM from 'react-dom/client';

function App() {
    return (
        <div className="container mx-auto px-4 py-8">
            <h1 className="text-3xl font-bold text-center mb-8">
                ProCook Recipe Manager
            </h1>
            <div className="text-center">
                <p className="text-gray-600 mb-4">
                    Welcome to your local development environment!
                </p>
                <p className="text-sm text-gray-500">
                    Laravel + React is ready for local development
                </p>
            </div>
        </div>
    );
}

const root = ReactDOM.createRoot(document.getElementById('app'));
root.render(<App />);