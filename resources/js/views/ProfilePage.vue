<template>
    <div class="profile-page">
        <h2>Profile</h2>
        <div v-if="user" class="profile-details">
            <img
                :src="
                    user.profile_picture
                        ? `/storage/${user.profile_picture}`
                        : '/assets/sportmalogo.jpeg'
                "
                alt="Profile Picture"
                class="profile-picture"
            />
            <p><strong>Name:</strong> {{ user.name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
            <p><strong>Role:</strong> {{ user.role }}</p>
            <p v-if="user.permissions">
                <strong>Permissions:</strong>
                {{ user.permissions.join(", ") }}
            </p>
            <button @click="openEditModal">Modifier votre Profile</button>
        </div>

        <!-- Edit Profile Modal -->
        <v-dialog v-model="editModal" max-width="500px">
            <v-card>
                <v-card-title>Modification du Profile</v-card-title>
                <v-card-text>
                    <v-form ref="editForm" @submit.prevent="updateProfile">
                        <v-text-field
                            v-model="form.name"
                            label="Name"
                            required
                        ></v-text-field>
                        <v-text-field
                            v-model="form.email"
                            label="Email"
                            required
                        ></v-text-field>
                        <v-text-field
                            v-model="form.role"
                            label="Role"
                            disabled
                        ></v-text-field>
                        <v-select
                            v-model="form.permissions"
                            label="Permissions"
                            :items="permissionsOptions"
                            multiple
                        ></v-select>
                        <v-file-input
                            v-model="form.profile_picture"
                            label="Télécharger la photo de profil"
                            accept="image/*"
                        ></v-file-input>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-btn color="blue darken-1" text @click="editModal = false"
                        >Annuler</v-btn
                    >
                    <v-btn color="blue darken-1" text @click="updateProfile"
                        >Enregistrer</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import { ref, computed, onMounted } from "vue";
import { useStore } from "vuex";
import axios from "axios";

export default {
    data() {
        return {
            editModal: false,
            form: {
                name: "",
                email: "",
                role: "",
                permissions: [],
                profile_picture: null,
            },
            permissionsOptions: [
                "Dashboard",
                "Comptabilite",
                "Configuration",
                "Calendrier",
                "Staff",
            ],
        };
    },
    computed: {
        user() {
            return this.$store.getters["auth/user"];
        },
    },
    methods: {
        openEditModal() {
            this.editModal = true;
            this.form = { ...this.user, profile_picture: null };
        },
        async updateProfile() {
            const formData = new FormData();
            formData.append("name", this.form.name);
            formData.append("email", this.form.email);
            formData.append("role", this.form.role);
            formData.append(
                "permissions",
                JSON.stringify(this.form.permissions)
            );
            if (this.form.profile_picture) {
                formData.append("profile_picture", this.form.profile_picture);
            }
            try {
                const response = await axios.post(
                    "/api/user/profile/update",
                    formData,
                    {
                        headers: { "Content-Type": "multipart/form-data" },
                    }
                );
                this.$store.dispatch("auth/fetchUserProfile");
                this.editModal = false;
            } catch (error) {
                console.error("Error updating profile:", error);
            }
        },
    },
    mounted() {
        this.$store.dispatch("auth/fetchUserProfile");
    },
};
</script>

<style scoped>
.profile-page {
    max-width: 600px;
    margin: auto;
}
.profile-picture {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 1rem;
}
</style>
