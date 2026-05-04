import axios from 'axios';

const api = axios.create({
  baseURL: 'https://tonnine-benin-backend.onrender.com/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor pour le token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('tontine_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// --- Auth ---
export const requestOtp = (phone) => api.post('/auth/request-otp', { phone });
export const verifyOtp = (phone, code) => api.post('/auth/verify-otp', { phone, code });

// --- User ---
export const getMe = () => api.get('/users/me');
export const updateMe = (data) => api.patch('/users/me', data);
export const getMyScore = () => api.get('/users/me/score');
export const getMyPayouts = () => api.get('/users/me/payouts');
export const getMyBalance = () => api.get('/users/me/balance');
export const getLeaderboard = () => api.get('/users/leaderboard');

// --- Groups ---
export const createGroup = (data) => api.post('/groups', data);
export const getGroups = () => api.get('/groups');
export const getGroupDetails = (groupId) => api.get(`/groups/${groupId}`);
export const inviteToGroup = (groupId, data) => api.post(`/groups/${groupId}/invite`, data);
export const joinGroup = (groupId, data) => api.post(`/groups/${groupId}/join`, data);
export const startGroup = (groupId) => api.post(`/groups/${groupId}/start`);
export const getGroupStats = (groupId) => api.get(`/groups/${groupId}/stats`);
export const proposeSwap = (groupId, data) => api.post(`/groups/${groupId}/propose-swap`, data);
export const submitBid = (groupId, data) => api.post(`/groups/${groupId}/bid`, data);
export const getBids = (groupId) => api.get(`/groups/${groupId}/bids`);

// --- Contributions ---
export const getPendingContributions = () => api.get('/contributions/pending');
export const initiatePayment = (contributionId) => api.post(`/contributions/${contributionId}/pay`);

// --- Payouts ---
export const getPayoutsList = () => api.get('/payouts');
export const getPayoutDetails = (payoutId) => api.get(`/payouts/${payoutId}`);

// --- Incidents ---
export const getIncidents = () => api.get('/incidents');
export const getIncidentDetails = (incidentId) => api.get(`/incidents/${incidentId}`);

// --- Notifications ---
export const getNotificationsList = () => api.get('/notifications');
export const markNotificationRead = (notificationId) => api.patch(`/notifications/${notificationId}/read`);

// --- Votes ---
export const getVotesList = () => api.get('/votes');
export const getVoteDetails = (voteId) => api.get(`/votes/${voteId}`);
export const castVote = (voteId, data) => api.post(`/votes/${voteId}/cast`, data);

export default api;
