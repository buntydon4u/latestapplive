import client from './client'

export const login = (username, password) => {
  return client.post('/auth/login', { username, password })
}

export const logout = () => {
  return client.post('/auth/logout')
}

export const verify = (token) => {
  return client.get('/auth/verify', {
    headers: { Authorization: `Bearer ${token}` }
  })
}

export const refreshToken = () => {
  return client.post('/auth/refresh')
}
