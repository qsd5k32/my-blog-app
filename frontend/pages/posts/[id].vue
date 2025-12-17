<script setup lang="ts">
import { ref, computed } from 'vue'
import type { Post, Comment } from '~/types'

definePageMeta({
  layout: 'default'
})

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { fetchPost, fetchPostComments, deletePost } = useApi()

const postId = computed(() => Number(route.params.id))

// Fetch post details
const { data: post, pending: postPending, error: postError } = await useAsyncData<Post>(
  `post-${postId.value}`,
  () => fetchPost(postId.value)
)

// Fetch post comments
const { data: comments, pending: commentsPending, refresh: refreshComments } = await useAsyncData<Comment[]>(
  `post-comments-${postId.value}`,
  () => fetchPostComments(postId.value)
)

// Set page title
useHead({
  title: computed(() => post.value ? `${post.value.title} - Blog` : 'Post - Blog')
})

const showEditModal = ref(false)
const showDeleteModal = ref(false)
const deleting = ref(false)

const isOwner = computed(() => {
  return authStore.isAuthenticated && post.value && authStore.user?.id === post.value.user_id
})

const handlePostUpdated = () => {
  showEditModal.value = false
  // Refresh post data
  refreshNuxtData(`post-${postId.value}`)
}

const handleDeletePost = async () => {
  try {
    deleting.value = true
    await deletePost(postId.value)
    router.push('/dashboard')
  } catch (err) {
    console.error('Failed to delete post:', err)
  } finally {
    deleting.value = false
    showDeleteModal.value = false
  }
}

const handleCommentAdded = () => {
  refreshComments()
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="postPending" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <LoadingSpinner size="large" />
    </div>

    <!-- Error State -->
    <div v-else-if="postError" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <ErrorMessage :message="postError.message" />
    </div>

    <!-- Post Content -->
    <article v-else-if="post" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Back Button -->
      <button
        @click="router.back()"
        class="mb-6 inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
      >
        <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back
      </button>

      <!-- Post Header -->
      <div class="bg-white rounded-lg shadow-sm p-8 mb-8">
        <div class="flex justify-between items-start mb-4">
          <h1 class="text-4xl font-bold text-gray-900">
            {{ post.title }}
          </h1>
          <div v-if="isOwner" class="flex space-x-2">
            <button
              @click="showEditModal = true"
              class="p-2 text-gray-400 hover:text-indigo-600 rounded-full hover:bg-gray-100"
            >
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
              </svg>
            </button>
            <button
              @click="showDeleteModal = true"
              class="p-2 text-gray-400 hover:text-red-600 rounded-full hover:bg-gray-100"
            >
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="flex items-center text-sm text-gray-500 mb-6">
          <span class="font-medium">{{ post.author?.name || 'Anonymous' }}</span>
          <span class="mx-2">•</span>
          <time>{{ new Date(post.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</time>
        </div>
        
        <div class="prose prose-lg max-w-none text-gray-700">
          {{ post.content }}
        </div>
      </div>

      <!-- Comments Section -->
      <div class="bg-white rounded-lg shadow-sm p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">
          Comments ({{ comments?.length || 0 }})
        </h2>

        <!-- Add Comment Form -->
        <CommentForm 
          v-if="authStore.isAuthenticated"
          :post-id="postId"
          @created="handleCommentAdded"
          class="mb-8"
        />
        <div v-else class="mb-8 p-4 bg-gray-50 rounded-lg text-center">
          <p class="text-gray-600">
            Please 
            <NuxtLink to="/login" class="text-indigo-600 hover:text-indigo-500 font-medium">
              sign in
            </NuxtLink>
            to leave a comment
          </p>
        </div>

        <!-- Comments List -->
        <div v-if="commentsPending" class="flex justify-center py-8">
          <LoadingSpinner />
        </div>
        <div v-else-if="comments && comments.length > 0" class="space-y-6">
          <CommentItem 
            v-for="comment in comments" 
            :key="comment.id" 
            :comment="comment"
            @deleted="refreshComments"
            @updated="refreshComments"
          />
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No comments yet. Be the first to comment!
        </div>
      </div>
    </article>

    <!-- Edit Post Modal -->
    <PostFormModal 
      v-if="showEditModal && post"
      :post="post"
      @close="showEditModal = false"
      @updated="handlePostUpdated"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-if="showDeleteModal"
      title="Delete Post"
      message="Are you sure you want to delete this post? This action cannot be undone."
      confirm-text="Delete"
      :loading="deleting"
      @confirm="handleDeletePost"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>