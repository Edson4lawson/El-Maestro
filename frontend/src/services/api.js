import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8080/api'

const api = axios.create({
  baseURL: API_URL,
  withCredentials: true, // Important pour les sessions PHP
  headers: {
    'Content-Type': 'application/json'
  }
})

api.interceptors.request.use(config => {
  // Utiliser le token admin stocké après le login
  const adminToken = localStorage.getItem('admin_token')
  if (adminToken) {
    config.headers.Authorization = `Bearer ${adminToken}`
  }
  return config
})

export const plateService = {
  getAll: () => api.get('/'), // The current index handles GET /
  addReview: (data) => api.post('/review', data)
}

export const orderService = {
  create: (data) => api.post('/order', data)
}

export const reservationService = {
  create: (data) => api.post('/reservation', data)
}

export const loyaltyService = {
  getUser: (phone) => api.get(`/loyalty?phone=${phone}`)
}

export const adminMenuService = {
  getAll: () => api.get('/index.php?route=admin/menu'),
  getStats: () => api.get('/index.php?route=admin/menu/stats'),
  getOne: (id) => api.get(`/index.php?route=admin/menu&id=${id}`),
  create: (data) => api.post('/index.php?route=admin/menu', data),
  update: (id, data) => api.put(`/index.php?route=admin/menu&id=${id}`, data),
  toggleStatus: (id) => api.put(`/index.php?route=admin/menu&action=toggle&id=${id}`),
  delete: (id) => api.delete(`/index.php?route=admin/menu&id=${id}`)
}

export default api
