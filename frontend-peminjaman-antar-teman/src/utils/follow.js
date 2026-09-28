import api from './api';

export default {
    browseUsers() {
        return api.get('/users/browse');
    },
    getDetail(id) {
        return api.get(`/users/${id}/detail`);
    },
    myFriends() {
        return api.get('/friends');
    },
    follow(id) {
        return api.post(`/users/${id}/follow`);
    },
    unfollow(id) {
        return api.delete(`/users/${id}/unfollow`);
    }
};