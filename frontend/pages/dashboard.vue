<script setup lang="ts">
import type { Post } from '~/types'

definePageMeta({
  layout: 'authenticated',
  middleware: 'auth'
})

useHead({
  title: 'Dashboard - Blog',
  meta: [
    { name: 'description', content: 'Manage your blog posts' }
  ]
})

const authStore = useAuthStore()
const { fetchUserPosts } = useApi()

const { data: posts, pending, error, refresh } = await useAsyncData<Post[]>(
  'user-posts',
  () => fetchUserPosts()
)

const showCreateModal = ref(false)

const handlePostCreated = () => {
  showCreateModal.value = false
  refresh()
}

const handlePostDeleted = () => {
  refresh()
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              My Posts
            </h1>
            <p class="mt-2 text-gray-600">
              Welcome back, {{ authStore.user?.name }}!
            </p>
          </div>
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Create Post
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="pending" class="flex justify-center items-center py-12">
        <LoadingSpinner size="large" />
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-12">
        <ErrorMessage 
          :message="error.message" 
          @retry="refresh"
        />
      </div>

      <!-- Posts List -->
      <div v-else-if="posts && posts.length > 0" class="space-y-6">
        <PostListItem 
          v-for="post in posts" 
          :key="post.id" 
          :post="post"
          :editable="true"
          @deleted="handlePostDeleted"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <EmptyState 
          title="No posts yet" 
          description="Create your first blog post to get started!"
        >
          <button
            @click="showCreateModal = true"
            class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
          >
            Create Your First Post
          </button>
        </EmptyState>
      </div>
    </div>

    <!-- Create Post Modal -->
    <PostFormModal 
      v-if="showCreateModal"
      @close="showCreateModal = false"
      @created="handlePostCreated"
    />
  </div>
</template>