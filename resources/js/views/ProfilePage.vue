<template>
    <div class="profile-page">
        <h2>User Profile</h2>
        <div v-if="user" class="profile-details">
            <img
                :src="`/storage/${user.profile_picture}`"
                alt="Profile Picture"
                v-if="user.profile_picture"
                class="profile-picture"
            />
            <p><strong>Name:</strong> {{ user.name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
            <p><strong>Role:</strong> {{ user.role }}</p>
            <!-- <p v-if="user.permissions">
                <strong>Permissions:</strong>
                {{ user.permissions.join(", ") }}
            </p> -->
            <button @click="openEditModal">Edit Profile</button>
        </div>

        <!-- Edit Profile Modal -->
        <v-dialog v-model="editModal" max-width="500px">
            <v-card>
                <v-card-title>Edit Profile</v-card-title>
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
                        <!-- File input for profile picture -->
                        <v-file-input
                            v-model="form.profile_picture"
                            label="Upload Profile Picture"
                            accept="image/*"
                        ></v-file-input>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-btn color="blue darken-1" text @click="editModal = false"
                        >Cancel</v-btn
                    >
                    <v-btn color="blue darken-1" text @click="updateProfile"
                        >Save</v-btn
                    >
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import { ref, onMounted } from "vue";
import axios from "axios";

export default {
    data() {
        return {
            user: {},
            editModal: false,
            form: {
                name: "",
                email: "",
                role: "",
                permissions: [],
                profile_picture: null, // Holds the file for upload
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
        // Generates the URL for the profile picture, default if not set
        profilePictureUrl() {
            return this.user.profile_picture || "/assets/sportmalogo.jpeg";
        },
    },
    methods: {
        async fetchUserProfile() {
            try {
                const response = await axios.get("/api/user/profile");
                if (typeof response.data.permissions === "string") {
                    response.data.permissions = JSON.parse(
                        response.data.permissions
                    );
                }
                this.user = response.data;
                console.log("User profile:", response.data);
                console.log(this.user.profile_picture);
                this.form = { ...response.data, profile_picture: null };
            } catch (error) {
                console.error("Error fetching user profile:", error);
            }
        },
        openEditModal() {
            this.editModal = true;
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
                this.user = { ...response.data }; // Update displayed data
                this.editModal = false;
            } catch (error) {
                console.error("Error updating profile:", error);
            }
        },
    },
    mounted() {
        this.fetchUserProfile();
        console.log("User:", this.user.value);
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
