<script setup lang="ts">
import type { Post } from '~/types'

// Meta tags for SEO
useHead({
  title: 'Blog - Home',
  meta: [
    { name: 'description', content: 'Welcome to our blog platform' }
  ]
})

// Fetch all posts
const { data: posts, pending, error, refresh } = await useAsyncData<Post[]>(
  'posts',
  () => $fetch('/api/posts', {
    baseURL: useRuntimeConfig().public.apiBase
  })
)
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
          Welcome to Our Blog
        </h1>
        <p class="text-xl text-gray-600">
          Discover amazing stories and insights
        </p>
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

      <!-- Posts Grid -->
      <div v-else-if="posts && posts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <PostCard 
          v-for="post in posts" 
          :key="post.id" 
          :post="post"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <EmptyState 
          title="No posts yet" 
          description="Be the first to create a post!"
        />
      </div>
    </div>
  </div>
</template>