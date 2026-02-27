import axios from 'axios'

const API_URL = 'http://localhost:8000/api' // Adjust to your PHP server path

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json'
  }
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

export default api
