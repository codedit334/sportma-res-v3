<template>
    <div class="profile-page">
        <h2>Profile</h2>
        <div v-if="user" class="profile-details">
            <img
                v-if="user.profile_picture"
                :src="'/storage/' + user.profile_picture"
                alt="Profile Picture"
                class="profile-picture"
            />
            <img
                v-else
                src="/resources/js/assets/user.png"
                alt="Default Profile Picture"
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
                            label="Nom"
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
                            v-if="!user.isAdmin"
                            label="Permissions"
                            :items="permissionsOptions"
                            disabled
                            multiple
                        ></v-select>
                        <v-file-input
                            v-model="form.profile_picture"
                            label="Télécharger la photo de profil"
                            accept="image/*"
                        ></v-file-input>

                        <!-- Change Password Section -->
                        <v-btn
                            text
                            color="primary"
                            @click="toggleChangePassword"
                        >
                            Changer le mot de passe
                        </v-btn>

                        <div v-if="changePassword">
                            <v-text-field
                                v-model="form.new_password"
                                label="Nouveau Mot de passe"
                                type="password"
                                required
                            ></v-text-field>
                            <v-text-field
                                v-model="form.confirm_new_password"
                                label="Confirmer le mot de passe"
                                type="password"
                                :error-messages="passwordError"
                                required
                            ></v-text-field>
                        </div>
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
import defaultUser from "@/assets/user.png";

export default {
    data() {
        return {
            editModal: false,
            changePassword: false,
            form: {
                name: "",
                email: "",
                role: "",
                permissions: [],
                profile_picture: null,
                new_password: "",
                confirm_new_password: "",
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
        passwordError() {
            if (this.form.new_password && this.form.confirm_new_password) {
                return this.form.new_password !== this.form.confirm_new_password
                    ? "Les mots de passe ne correspondent pas."
                    : "";
            }
            return "";
        },
    },
    methods: {
        openEditModal() {
            this.editModal = true;
            this.form = { ...this.user, profile_picture: null };
        },
        toggleChangePassword() {
            this.changePassword = !this.changePassword;
        },
        async updateProfile() {
            // Prevent update if password error exists
            if (this.passwordError) {
                return;
            }

            const formData = new FormData();
            formData.append("name", this.form.name);
            formData.append("email", this.form.email);
            formData.append("role", this.form.role);

            // Append each permission individually to handle array format correctly
            this.form.permissions.forEach((permission) => {
                formData.append("permissions[]", permission);
            });

            if (this.form.profile_picture) {
                formData.append("profile_picture", this.form.profile_picture);
            }

            if (this.changePassword) {
                formData.append("new_password", this.form.new_password);
                formData.append(
                    "new_password_confirmation",
                    this.form.confirm_new_password
                );
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
                this.changePassword = false;
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
