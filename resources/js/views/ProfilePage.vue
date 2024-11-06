<template>
    <div class="profile-page">
        <h2>User Profile</h2>
        <div v-if="user" class="profile-details">
            <p><strong>Name:</strong> {{ user.name }}</p>
            <p><strong>Email:</strong> {{ user.email }}</p>
            <p><strong>Role:</strong> {{ user.role }}</p>
            <p v-if="user.permissions">
                <strong>Permissions:</strong>
                {{ user.permissions.join(", ") }}
            </p>
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
    methods: {
        async fetchUserProfile() {
            try {
                const response = await axios.get("/api/user/profile");
                // Convert permissions to an array if it's a string
                if (typeof response.data.permissions === "string") {
                    response.data.permissions = JSON.parse(
                        response.data.permissions
                    );
                }
                console.log("Fetched user profile:", response.data);

                this.user = response.data;

                this.form = { ...response.data };
            } catch (error) {
                console.error("Error fetching user profile:", error);
            }
        },
        openEditModal() {
            this.editModal = true;
        },
        async updateProfile() {
            try {
                await axios.put("/api/user/profile/update", this.form);
                this.user = { ...this.form }; // Update displayed data
                this.editModal = false;
            } catch (error) {
                console.error("Error updating profile:", error);
            }
        },
    },
    mounted() {
        console.log("Component mounted.");
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
