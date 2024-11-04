<template>
    <v-card title="User Management" flat class="my-5 mx-5">
        <template v-slot:text>
            <v-btn color="primary" @click="openAddUserModal">Add User</v-btn>
        </template>

        <!-- User Data Table -->
        <v-data-table :headers="userHeaders" :items="users" :search="search">
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
                    <v-text-field
                        v-model="newUser.password"
                        label="Password"
                        type="password"
                        required
                    ></v-text-field>
                    <v-text-field
                        v-model="passwordConfirmation"
                        label="Confirm Password"
                        type="password"
                        :error-messages="passwordError"
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
                        :items="availablePermissions"
                        label="Permissions"
                        multiple
                        required
                    ></v-select>
                </v-card-text>
                <v-card-actions>
                    <v-btn text @click="addUser">Add</v-btn>
                    <v-btn text @click="closeAddUserModal">Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Edit User Dialog -->
        <v-dialog v-model="editUserDialog" max-width="600px">
            <v-card>
                <v-card-title>Edit User</v-card-title>
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
                    <v-select
                        v-model="newUser.permissions"
                        :items="availablePermissions"
                        label="Permissions"
                        multiple
                        required
                    ></v-select>
                </v-card-text>
                <v-card-actions>
                    <v-btn text @click="saveUserChanges">Save</v-btn>
                    <v-btn text @click="closeEditUserModal">Cancel</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-card>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";

const users = ref([]);
const addUserDialog = ref(false);
const editUserDialog = ref(false);
const newUser = ref({
    fullName: "",
    email: "",
    role: "",
    password: "",
    permissions: [],
});
const passwordConfirmation = ref("");

const roles = ["Admin", "User"];
const availablePermissions = [
    "Dashboard",
    "Contabilite",
    "Configuration",
    "Calendrier",
    "Staff",
];
const userHeaders = [
    { align: "start", key: "name", title: "Full Name" },
    { key: "email", title: "Email" },
    { key: "role", title: "Role" },
    { key: "permissions", title: "Permissions" },
    { key: "actions", title: "Actions", sortable: false },
];

const passwordError = computed(() => {
    return newUser.value.password !== passwordConfirmation.value
        ? "Passwords do not match"
        : "";
});

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
    newUser.value.permissions = ["Calendrier"];
};

const closeAddUserModal = () => {
    addUserDialog.value = false;
    newUser.value = {
        fullName: "",
        email: "",
        role: "",
        password: "",
        permissions: [],
    };
    passwordConfirmation.value = "";
};

const addUser = async () => {
    if (passwordError.value) {
        return;
    }
    try {
        const payload = {
            full_name: newUser.value.fullName,
            email: newUser.value.email,
            password: newUser.value.password,
            password_confirmation: passwordConfirmation.value,
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
    newUser.value = { ...user };
    editUserDialog.value = true;
};

const closeEditUserModal = () => {
    editUserDialog.value = false;
    newUser.value = { fullName: "", email: "", role: "", permissions: [] };
};

const saveUserChanges = async () => {
    try {
        const response = await axios.put(
            `/api/users/${newUser.value.id}`,
            newUser.value
        );
        const index = users.value.findIndex((u) => u.id === newUser.value.id);
        if (index !== -1) {
            users.value[index] = response.data;
        }
        closeEditUserModal();
    } catch (error) {
        console.error("Error updating user:", error);
    }
};

const deleteUser = async (user) => {
    try {
        await axios.delete(`/api/users/${user.id}`);
        users.value = users.value.filter((u) => u.id !== user.id);
    } catch (error) {
        console.error("Error deleting user:", error);
    }
};
</script>
