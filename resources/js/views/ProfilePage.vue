<template>
    <div class="profile-page">
      <h2>User Profile</h2>
      <div class="profile-details">
        <p><strong>Name:</strong> {{ user.name }}</p>
        <p><strong>Email:</strong> {{ user.email }}</p>
        <p><strong>Role:</strong> {{ user.role }}</p>
        <p><strong>Permissions:</strong> {{ user.permissions.join(', ') }}</p>
        <button @click="openEditModal">Edit Profile</button>
      </div>
  
      <!-- Edit Profile Modal -->
      <v-dialog v-model="editModal" max-width="500px">
        <v-card>
          <v-card-title>Edit Profile</v-card-title>
          <v-card-text>
            <v-form ref="editForm" @submit.prevent="updateProfile">
              <v-text-field v-model="form.name" label="Name" required></v-text-field>
              <v-text-field v-model="form.email" label="Email" required></v-text-field>
              <v-text-field v-model="form.role" label="Role" disabled></v-text-field>
              <v-select v-model="form.permissions" label="Permissions" :items="permissionsOptions" multiple></v-select>
            </v-form>
          </v-card-text>
          <v-card-actions>
            <v-btn color="blue darken-1" text @click="editModal = false">Cancel</v-btn>
            <v-btn color="blue darken-1" text @click="updateProfile">Save</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  
  export default {
    data() {
      return {
        user: {},
        editModal: false,
        form: {
          name: '',
          email: '',
          role: '',
          permissions: [],
        },
        permissionsOptions: ['Dashboard', 'Comptabilite', 'Configuration', 'Calendrier', 'Staff'],
      };
    },
    methods: {
      async fetchUserProfile() {
        try {
          const response = await axios.get('/user/profile');
          this.user = response.data;
          this.form = { ...response.data };
        } catch (error) {
          console.error('Error fetching user profile:', error);
        }
      },
      openEditModal() {
        this.editModal = true;
      },
      async updateProfile() {
        try {
          await axios.put('/user/profile/update', this.form);
          this.user = { ...this.form }; // Update displayed data
          this.editModal = false;
        } catch (error) {
          console.error('Error updating profile:', error);
        }
      },
    },
    mounted() {
      this.fetchUserProfile();
    },
  };
  </script>
  
  <style scoped>
  .profile-page {
    max-width: 600px;
    margin: auto;
  }
  </style>
  