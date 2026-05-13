import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor pour le token et la langue
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('tontine_token');
  const user = JSON.parse(localStorage.getItem('tontine_user') || '{}');
  
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  
  if (user.preferred_language) {
    config.params = { ...config.params, locale: user.preferred_language };
  }
  
  return config;
});

// --- Auth ---
export const requestOtp = (email, locale = 'fr') => api.post('/auth/request-otp', { email, locale });
export const verifyOtp = (email, code) => api.post('/auth/verify-otp', { email, code });

// --- User ---
export const getMe = () => api.get('/users/me');
export const updateProfile = (data) => api.post('/user/profile', data);
export const updateMe = updateProfile; // Alias pour compatibilité
export const getMyScore = () => api.get('/users/me/score');
export const getMyBalance = () => api.get('/users/me/balance');
export const getLeaderboard = () => api.get('/leaderboard');

// --- AI (YAO) ---
export const chatWithYao = (message) => api.post('/ai/chat', { message });

// --- Groups ---
export const createGroup = (data) => api.post('/groups', data);
export const getGroups = () => api.get('/groups');
export const getGroupDetails = (groupId) => api.get(`/groups/${groupId}`);
export const inviteToGroup = (groupId, data) => api.post(`/groups/${groupId}/invite`, data);
export const joinGroup = (groupId, data) => api.post(`/groups/${groupId}/join`, data);
export const startGroup = (groupId) => api.post(`/groups/${groupId}/start`);
export const getGroupStats = (groupId) => api.get(`/groups/${groupId}/stats`);
export const submitBid = (groupId, data) => api.post(`/groups/${groupId}/bid`, data);
export const proposeSwap = (groupId, data) => api.post(`/groups/${groupId}/swap`, data);

// --- Votes ---
export const castVote = (groupId, data) => api.post(`/groups/${groupId}/vote`, data);

// --- Contributions ---
export const getPendingContributions = () => api.get('/contributions/pending');
export const initiatePayment = (contributionId) => api.post(`/contributions/${contributionId}/pay`);

// --- Notifications ---
export const getNotificationsList = () => api.get('/notifications');

export default api;
