import client from './client'

export const getStatement = (ledgerId, filters = {}) => {
  return client.get(`/reports/statement/${ledgerId}`, { params: filters })
}

export const getSummary = (ledgerId, filters = {}) => {
  return client.get(`/reports/summary/${ledgerId}`, { params: filters })
}

export const getHistory = (ledgerId, filters = {}) => {
  return client.get(`/reports/history/${ledgerId}`, { params: filters })
}

export const getAnalytics = (filters = {}) => {
  return client.get('/reports/analytics', { params: filters })
}

export const exportReport = (reportType, filters = {}) => {
  return client.get(`/reports/export/${reportType}`, {
    params: filters,
    responseType: 'blob'
  })
}
