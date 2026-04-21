<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" v-if="open" @click.self="$emit('close')">
    <div class="bg-zinc-800 rounded-xl p-8 w-full max-w-md shadow-2xl">
      <h2 class="text-2xl font-bold text-white mb-6">Edit Profile</h2>
      
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-white mb-2">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full px-4 py-2 bg-zinc-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-white mb-2">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full px-4 py-2 bg-zinc-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-white mb-2">New Password (optional)</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full px-4 py-2 bg-zinc-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
            placeholder="Leave blank to keep current password"
          />
        </div>

        <div v-if="form.password">
          <label class="block text-sm font-medium text-white mb-2">Confirm New Password</label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="w-full px-4 py-2 bg-zinc-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
            :required="!!form.password"
          />
        </div>

        <div class="flex gap-3 pt-4">
          <button
            type="submit"
            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
          >
            Save Changes
          </button>
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
  open: boolean
  user: { name: string; email: string } | null
}>()

const emit = defineEmits<{
  close: []
  save: [user: { name: string; email: string; password?: string; password_confirmation?: string }]
}>()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

watch(() => props.user, (newUser) => {
  if (newUser) {
    form.value.name = newUser.name || ''
    form.value.email = newUser.email || ''
    form.value.password = ''
    form.value.password_confirmation = ''
  }
}, { immediate: true })

const submit = () => {
  const updateData: any = {
    name: form.value.name,
    email: form.value.email
  }
  
  // Only include password if user entered one
  if (form.value.password) {
    updateData.password = form.value.password
    updateData.password_confirmation = form.value.password_confirmation
  }
  
  emit('save', updateData)
}
</script>
