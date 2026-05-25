import React from 'react'
import { BrowserRouter as Router } from 'react-router-dom'
import Routes from './routes'
import { AuthProvider } from './context/AuthContext'
import { ThemeProvider } from './context/ThemeContext'

function App() {
  return (
    <ThemeProvider>
      <AuthProvider>
        <Router>
          <Routes />
        </Router>
      </AuthProvider>
    </ThemeProvider>
  )
}

export default App
