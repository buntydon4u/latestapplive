import client from './client'

export const getEntries = (filters = {}) => {
  return client.get('/entries', { params: filters })
}

export const getEntry = (id) => {
  return client.get(`/entries/${id}`)
}

export const createEntry = (data) => {
  return client.post('/entries', data)
}

export const updateEntry = (id, data) => {
  return client.put(`/entries/${id}`, data)
}

export const deleteEntry = (id) => {
  return client.delete(`/entries/${id}`)
}

export const exportEntries = (filters = {}) => {
  return client.get('/entries/export', { 
    params: filters,
    responseType: 'blob'
  })
}
