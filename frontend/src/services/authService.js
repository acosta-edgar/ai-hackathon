import api from './api';

const authService = {
  /**
   * Register a new user
   * @param {Object} userData - User registration data
   * @returns {Promise<Object>} - User data and auth token
   */
  async register(userData) {
    try {
      const response = await api.post('/auth/register', userData);
      return response.data;
    } catch (error) {
      console.error('Registration error:', error);
      throw error;
    }
  },

  /**
   * Login user
   * @param {string} email - User email
   * @param {string} password - User password
   * @returns {Promise<Object>} - User data and auth token
   */
  async login(email, password) {
    try {
      const response = await api.post('/auth/login', { email, password });
      return response.data;
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  },

  /**
   * Logout user
   * @returns {Promise<void>}
   */
  async logout() {
    try {
      await api.post('/auth/logout');
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  },

  /**
   * Refresh authentication token
   * @returns {Promise<Object>} - New auth token
   */
  async refreshToken() {
    try {
      const response = await api.post('/auth/refresh-token');
      return response.data;
    } catch (error) {
      console.error('Token refresh error:', error);
      throw error;
    }
  },

  /**
   * Request password reset
   * @param {string} email - User email
   * @returns {Promise<void>}
   */
  async forgotPassword(email) {
    try {
      await api.post('/auth/forgot-password', { email });
    } catch (error) {
      console.error('Forgot password error:', error);
      throw error;
    }
  },

  /**
   * Reset password with token
   * @param {string} token - Password reset token
   * @param {string} email - User email
   * @param {string} password - New password
   * @param {string} passwordConfirmation - Password confirmation
   * @returns {Promise<void>}
   */
  async resetPassword(token, email, password, passwordConfirmation) {
    try {
      await api.post('/auth/reset-password', {
        token,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
    } catch (error) {
      console.error('Password reset error:', error);
      throw error;
    }
  },

  /**
   * Get current user profile
   * @returns {Promise<Object>} - User profile data
   */
  async getProfile() {
    try {
      const response = await api.get('/user/profile');
      return response.data;
    } catch (error) {
      console.error('Error fetching user profile:', error);
      throw error;
    }
  },

  /**
   * Update user profile
   * @param {Object} profileData - Updated profile data
   * @returns {Promise<Object>} - Updated user profile
   */
  async updateProfile(profileData) {
    try {
      const response = await api.put('/user/profile', profileData);
      return response.data;
    } catch (error) {
      console.error('Error updating profile:', error);
      throw error;
    }
  },

  /**
   * Update user password
   * @param {string} currentPassword - Current password
   * @param {string} newPassword - New password
   * @param {string} newPasswordConfirmation - New password confirmation
   * @returns {Promise<void>}
   */
  async updatePassword(currentPassword, newPassword, newPasswordConfirmation) {
    try {
      await api.put('/user/password', {
        current_password: currentPassword,
        password: newPassword,
        password_confirmation: newPasswordConfirmation,
      });
    } catch (error) {
      console.error('Error updating password:', error);
      throw error;
    }
  },

  /**
   * Upload profile picture
   * @param {File} file - Image file
   * @returns {Promise<Object>} - Uploaded file information
   */
  async uploadProfilePicture(file) {
    try {
      const formData = new FormData();
      formData.append('avatar', file);

      const response = await api.post('/user/avatar', formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      return response.data;
    } catch (error) {
      console.error('Error uploading profile picture:', error);
      throw error;
    }
  },
};

export default authService;
