<template>
    <v-card title="Gestion des utilisateurs" flat class="my-5 mx-5">
        <template v-slot:text>
            <v-btn color="primary" @click="openAddUserModal">
                Ajout utilisateur
            </v-btn>
        </template>

        <!-- Search Input -->
        <v-text-field
            v-model="search"
            label="Recherche"
            class="my-3 mx-4"
            clearable
        ></v-text-field>

        <!-- User Data Table -->
        <v-data-table
            :headers="userHeaders"
            :loading="loading"
            :items="filteredUsers"
            :search="search"
        >
            <!-- Log users -->
            <template v-slot:item.actions="{ item }">
                <v-btn icon color="blue" @click="openEditUserModal(item)">
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn icon color="red" @click="deleteUser(item)">
                    <v-icon>mdi-delete</v-icon>
                </v-btn>
            </template>
        </v-data-table>

        <!-- Add User Dialog -->
        <v-dialog v-model="addUserDialog" max-width="600px">
            <v-card>
                <v-card-title>Ajout utilisateur</v-card-title>
                <v-card-text>
                    <v-text-field
                        v-model="newUser.name"
                        label="Nom Complet"
                        required
                    ></v-text-field>
                    <v-text-field
                        v-model="newUser.email"
                        label="Email"
                        required
                    ></v-text-field>
                    <v-text-field
                        v-model="newUser.password"
                        label="Mot de passe"
                        type="password"
                        required
                    ></v-text-field>
                    <v-text-field
                        v-model="passwordConfirmation2"
                        label="Confirmer le mot de passe"
                        type="password"
                        :error-messages="passwordError2"
                        required
                    ></v-text-field>
                    <v-select
                        v-model="newUser.role"
                        :items="roles"
                        label="Role"
                        required
                    ></v-select>
                    <v-select
                        v-model="newUser.permissions"
                        :items="availablePermissions2"
                        item-text="name"
                        item-value="id"
                        label="Permissions"
                        multiple
                        chips
                        clearable
                    ></v-select>
                </v-card-text>
                <v-card-actions>
                    <v-btn text @click="addUser">Ajouter</v-btn>
                    <v-btn text @click="closeAddUserModal">Annuler</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Edit User Dialog -->
        <v-dialog v-model="editUserDialog" max-width="600px">
            <v-card>
                <v-card-title>Modification utilisateur</v-card-title>
                <v-card-text>
                    <v-text-field
                        v-model="newUser.name"
                        label="Nom Complet"
                        required
                    ></v-text-field>
                    <v-text-field
                        v-model="newUser.email"
                        label="Email"
                        required
                    ></v-text-field>
                    <v-select
                        v-model="newUser.role"
                        :items="roles"
                        label="Role"
                        required
                    ></v-select>
                    <v-select
                        v-model="newUser.permissions"
                        :items="availablePermissions2"
                        label="Permissions"
                        multiple
                        chips
                        clearable
                    ></v-select>

                    <!-- Change Password Button -->
                    <v-btn text color="primary" @click="togglePasswordFields">
                        Changer le mot de passe
                    </v-btn>

                    <!-- Password Fields, only shown if showPasswordFields is true -->
                    <div v-if="showPasswordFields">
                        <v-text-field
                            v-model="newUser.newPassword"
                            label="Nouveau mot de passe"
                            type="password"
                            required
                        ></v-text-field>
                        <v-text-field
                            v-model="passwordConfirmation"
                            label="Confirmer le mot de passe"
                            type="password"
                            :error-messages="passwordError"
                            required
                        ></v-text-field>
                    </div>
                </v-card-text>
                <v-card-actions>
                    <v-btn text @click="saveUserChanges">Enregister</v-btn>
                    <v-btn text @click="closeEditUserModal">Annuler</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";

const users = ref([]);
const loading = ref(false);
const addUserDialog = ref(false);
const editUserDialog = ref(false);
const newUser = ref({
    name: "",
    email: "",
    role: "",
    password: "",
    permissions: [],
    newPassword: "", // Add field for new password
});
const passwordConfirmation = ref("");
const passwordConfirmation2 = ref("");
const showPasswordFields = ref(false); // Ref to toggle password fields
const search = ref(""); // Reactive variable for search input

const roles = ["Responsable", "Receptionniste","Comptable", "Coordinateur d’Evenements"];
const availablePermissions = [
    { id: "dashboard", name: "Dashboard" },
    { id: "comptabilite", name: "Comptabilite" },
    { id: "configuration", name: "Configuration" },
    { id: "calendrier", name: "Calendrier" },
    { id: "staff", name: "Staff" },
];
const availablePermissions2 = [
    "Dashboard",
    "Comptabilite",
    "Configuration",
    "Calendrier",
    "Staff",
];

const userHeaders = [
    { align: "start", key: "name", title: "Nom Complet" },
    { key: "email", title: "Email" },
    { key: "role", title: "Role" },
    { key: "permissions", title: "Permissions" },
    { key: "actions", title: "Actions", sortable: false },
];

// Computed property to filter users based on search input
const filteredUsers = computed(() => {
    if (!search.value) return users.value; // Return all users if search is empty
    return users.value.filter((user) => {
        return (
            user.name.toLowerCase().includes(search.value.toLowerCase()) ||
            user.email.toLowerCase().includes(search.value.toLowerCase())
        );
    });
});

const passwordError = computed(() => {
    if (showPasswordFields.value) {
        return newUser.value.newPassword !== passwordConfirmation.value
            ? "Les mots de passe ne correspondent pas."
            : "";
    }
    return "";
});

const passwordError2 = computed(() => {
    if (newUser.value.password) {
        return newUser.value.password !== passwordConfirmation2.value
            ? "Les mots de passe ne correspondent pas."
            : "";
    }
    return "";
});

const fetchUsers = async () => {
    loading.value = true; // Set loading to true when fetching starts
    try {
        const response = await axios.get("/api/users");
        users.value = response.data || [];
    } catch (error) {
        console.error("Error fetching users:", error);
    } finally {
        loading.value = false; // Set loading to false after fetching
    }
};

onMounted(() => {
    fetchUsers();
});

const openAddUserModal = () => {
    addUserDialog.value = true;
    newUser.value = {
        name: "",
        email: "",
        role: "",
        password: "",
        permissions: [],
    };
    newUser.value.permissions = ["Calendrier"];
};

const closeAddUserModal = () => {
    addUserDialog.value = false;
    newUser.value = {
        name: "",
        email: "",
        role: "",
        password: "",
        permissions: [],
    };
    passwordConfirmation.value = "";
    fetchUsers();
};

const addUser = async () => {
    if (passwordError2.value) {
        return;
    }
    try {
        const payload = {
            full_name: newUser.value.name,
            email: newUser.value.email,
            password: newUser.value.password,
            password_confirmation: passwordConfirmation2.value,
            role: newUser.value.role,
            permissions: newUser.value.permissions,
        };
        const response = await axios.post("/api/users", payload);
        users.value.push(response.data);
        closeAddUserModal();
    } catch (error) {
        console.error("Error adding user:", error);
    }
};

const openEditUserModal = (user) => {
    newUser.value = {
        ...user,
        newPassword: "", // Reset newPassword
        // Parse permissions from JSON string to array
        permissions: typeof user.permissions === 'string' ? JSON.parse(user.permissions) : user.permissions,
    };
    editUserDialog.value = true;
    showPasswordFields.value = false; // Hide password fields initially
};

const closeEditUserModal = () => {
    editUserDialog.value = false;
    newUser.value = {
        name: "",
        email: "",
        role: "",
        password: "",
        permissions: [],
    };
    passwordConfirmation.value = "";
};

const saveUserChanges = async () => {
    try {
        const payload = {
            ...newUser.value,
            password: newUser.value.newPassword || undefined,
        };
        const response = await axios.put(
            `/api/users/${newUser.value.id}`,
            payload
        );
        const index = users.value.findIndex((u) => u.id === newUser.value.id);
        users.value[index] = response.data; // Update the user in the users array
        closeEditUserModal();
    } catch (error) {
        console.error("Error updating user:", error);
    }
};

const deleteUser = async (user) => {
    const confirmed = confirm(
        `Êtes-vous sûr de vouloir supprimer ${user.name} ?`
    );
    if (confirmed) {
        try {
            await axios.delete(`/api/users/${user.id}`);
            users.value = users.value.filter((u) => u.id !== user.id); // Remove user from array
        } catch (error) {
            console.error("Error deleting user:", error);
        }
    }
};

const togglePasswordFields = () => {
    showPasswordFields.value = !showPasswordFields.value; // Toggle password fields
};
</script>

<style scoped>
.v-data-table {
    margin-top: 20px;
}
</style>
