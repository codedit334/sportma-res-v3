<template>
    <v-card title="User Management" flat class="my-5 mx-5">
        <template v-slot:text>
            <v-btn color="primary" @click="openAddUserModal">Add User</v-btn>
        </template>

        <!-- Conditional rendering of the data table or message -->
        <div v-if="users.value && users.value.length > 0">
            <v-data-table
                :headers="userHeaders"
                :items="users"
                :search="search"
                :no-data-text="'No users available'"
                :loading-text="'Loading...'"
            ></v-data-table>
        </div>
        <div v-else>
            <p>No users available</p>
        </div>

        <v-dialog v-model="addUserDialog" max-width="600px">
            <v-card>
                <v-card-title>Add User</v-card-title>
                <v-card-text>
                    <v-text-field
                        v-model="newUser.fullName"
                        label="Full Name"
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
                </v-card-text>
                <v-card-actions>
                    <v-btn text @click="addUser">Add</v-btn>
                    <v-btn text @click="closeAddUserModal">Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-dialog v-model="permissionsDialog" max-width="600px">
            <v-card>
                <v-card-title>Permissions</v-card-title>
                <v-card-text>
                    <v-checkbox
                        v-for="permission in availablePermissions"
                        :key="permission.id"
                        v-model="selectedPermissions"
                        :label="permission.name"
                    ></v-checkbox>
                </v-card-text>
                <v-card-actions>
                    <v-btn text @click="savePermissions">Save</v-btn>
                    <v-btn text @click="closePermissionsDialog">Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const users = ref([]);
const addUserDialog = ref(false);
const permissionsDialog = ref(false);
const newUser = ref({ fullName: "", email: "", role: "" });
const selectedPermissions = ref([]);

const roles = ["Admin", "User"];
const availablePermissions = [
    { id: 1, name: "Edit" },
    { id: 2, name: "Delete" },
];

const userHeaders = [
    { align: "start", key: "nom", title: "Nom" },
    { key: "categorie", title: "Categorie" },
    { key: "terrain", title: "Terrain" },
    { key: "prix", title: "Prix (DH)" },
    { key: "statut", title: "Statut" },
];

const fetchUsers = async () => {
    try {
        const response = await axios.get("/api/users");
        users.value = response.data || [];
    } catch (error) {
        console.error("Error fetching users:", error);
    }
};

onMounted(() => {
    fetchUsers();
});

const openAddUserModal = () => {
    addUserDialog.value = true;
};

const closeAddUserModal = () => {
    addUserDialog.value = false;
};

const addUser = async () => {
    try {
        const response = await axios.post("/api/users", newUser.value);
        users.value.push(response.data);
        closeAddUserModal();
    } catch (error) {
        console.error("Error adding user:", error);
    }
};

const openPermissionsModal = () => {
    permissionsDialog.value = true;
};

const closePermissionsDialog = () => {
    permissionsDialog.value = false;
};

const savePermissions = async () => {
    try {
        await axios.put(
            `/api/users/${newUser.value.id}/permissions`,
            selectedPermissions.value
        );
        closePermissionsDialog();
    } catch (error) {
        console.error("Error saving permissions:", error);
    }
};
</script>
