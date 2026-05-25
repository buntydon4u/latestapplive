import client from './client'

export const getLedgers = (filters = {}) => {
  return client.get('/ledgers', { params: filters })
}

export const getLedger = (id) => {
  return client.get(`/ledgers/${id}`)
}

export const getLedgerChildren = (parentId) => {
  return client.get(`/ledgers/${parentId}/children`)
}

export const createLedger = (data) => {
  return client.post('/ledgers', data)
}

export const updateLedger = (id, data) => {
  return client.put(`/ledgers/${id}`, data)
}

export const deleteLedger = (id) => {
  return client.delete(`/ledgers/${id}`)
}
