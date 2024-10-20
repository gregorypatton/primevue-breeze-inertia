import axios from 'axios';

const api = axios.create({
  baseURL: '/api', // Assuming your API is served from the /api route
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

export default api;
