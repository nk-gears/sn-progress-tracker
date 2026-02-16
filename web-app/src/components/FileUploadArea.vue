<template>
  <div class="file-upload-area">
    <label class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="!required" class="text-gray-500">({{ $t('form.optional', 'Optional') }})</span>
    </label>

    <!-- Drag and Drop Area -->
    <div
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      :class="[
        'border-2 border-dashed rounded-lg p-6 text-center transition-colors',
        isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'
      ]"
    >
      <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      <p class="text-gray-600 mb-2">
        {{ $t('file.dragDrop', 'Drag and drop files here, or') }}
        <button
          type="button"
          @click="$refs.fileInput?.click()"
          class="text-blue-600 hover:text-blue-700 font-semibold underline"
        >
          {{ $t('file.browse', 'browse') }}
        </button>
      </p>
      <p class="text-sm text-gray-500">
        {{ maxFiles > 1 ? $t('file.maxFiles', 'Max') + ' ' + maxFiles + ' ' + $t('file.files', 'files') : $t('file.oneFile', 'One file') }}
        • {{ $t('file.maxSize', 'Max') }} {{ maxSizeMB }}MB {{ $t('file.each', 'each') }}
      </p>
    </div>

    <!-- File Input (Hidden) -->
    <input
      ref="fileInput"
      type="file"
      :multiple="maxFiles > 1"
      :accept="acceptedTypes.join(',')"
      class="hidden"
      @change="handleFileSelect"
    />

    <!-- Error Message -->
    <div v-if="uploadError" class="mt-2 text-sm text-red-600">
      {{ uploadError }}
    </div>

    <!-- File Previews -->
    <div v-if="selectedFiles.length > 0" class="mt-4">
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <div
          v-for="(file, index) in selectedFiles"
          :key="index"
          class="relative group"
        >
          <!-- Image Preview -->
          <img
            v-if="file.preview && file.type.startsWith('image/')"
            :src="file.preview"
            :alt="`Preview ${index + 1}`"
            class="w-full h-24 object-cover rounded-lg"
          />

          <!-- Video Preview -->
          <div
            v-else
            class="w-full h-24 bg-gray-200 rounded-lg flex items-center justify-center"
          >
            <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
              <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
            </svg>
          </div>

          <!-- File Info -->
          <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 rounded-lg flex items-center justify-center transition-all">
            <button
              type="button"
              @click="removeFile(index)"
              class="opacity-0 group-hover:opacity-100 bg-red-500 text-white p-2 rounded-full transition-all"
              :title="$t('file.remove', 'Remove')"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- File Size -->
          <p class="text-xs text-gray-500 mt-1 truncate">{{ formatFileSize(file.size) }}</p>
        </div>
      </div>

      <!-- Encoding Progress -->
      <div v-if="isEncoding" class="mt-4">
        <p class="text-sm text-gray-600 mb-2">{{ $t('file.encoding', 'Preparing files for upload...') }}</p>
        <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-blue-600 transition-all" :style="{ width: `${encodingProgress}%` }"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

interface FileWithPreview {
  file: File
  preview?: string
  type: string
  size: number
}

interface Props {
  label?: string
  maxFiles?: number
  maxSizeMB?: number
  acceptedTypes?: string[]
  required?: boolean
}

interface Emits {
  (e: 'files-changed', files: File[]): void
}

const props = withDefaults(defineProps<Props>(), {
  label: 'Files',
  maxFiles: 1,
  maxSizeMB: 10,
  acceptedTypes: () => ['image/*', 'video/*'],
  required: false
})

const emit = defineEmits<Emits>()

const fileInput = ref<HTMLInputElement>()
const isDragging = ref(false)
const selectedFiles = ref<FileWithPreview[]>([])
const uploadError = ref<string | null>(null)
const isEncoding = ref(false)
const encodingProgress = ref(0)

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i]
}

const validateFile = (file: File): string | null => {
  // Check file type
  const isAcceptedType = props.acceptedTypes.some(type => {
    if (type.endsWith('/*')) {
      return file.type.startsWith(type.slice(0, -2))
    }
    return file.type === type
  })

  if (!isAcceptedType) {
    return `File type not accepted. Accepted: ${props.acceptedTypes.join(', ')}`
  }

  // Check file size
  const maxSizeBytes = props.maxSizeMB * 1024 * 1024
  if (file.size > maxSizeBytes) {
    return `File too large. Max: ${props.maxSizeMB}MB`
  }

  return null
}

const addFile = (file: File) => {
  const error = validateFile(file)
  if (error) {
    uploadError.value = error
    return false
  }

  if (selectedFiles.value.length >= props.maxFiles) {
    uploadError.value = `Maximum ${props.maxFiles} file(s) allowed`
    return false
  }

  uploadError.value = null

  const fileData: FileWithPreview = {
    file,
    type: file.type,
    size: file.size
  }

  // Create preview for images
  if (file.type.startsWith('image/')) {
    const reader = new FileReader()
    reader.onload = (e) => {
      fileData.preview = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }

  selectedFiles.value.push(fileData)
  emitFilesChanged()
  return true
}

const handleDrop = (e: DragEvent) => {
  isDragging.value = false
  const files = e.dataTransfer?.files
  if (files) {
    for (let i = 0; i < files.length; i++) {
      addFile(files[i])
    }
  }
}

const handleFileSelect = (e: Event) => {
  const files = (e.target as HTMLInputElement).files
  if (files) {
    for (let i = 0; i < files.length; i++) {
      addFile(files[i])
    }
  }
}

const removeFile = (index: number) => {
  selectedFiles.value.splice(index, 1)
  emitFilesChanged()
}

const emitFilesChanged = () => {
  const files = selectedFiles.value.map(f => f.file)
  emit('files-changed', files)
}

watch(
  () => props.maxFiles,
  (newMax) => {
    if (selectedFiles.value.length > newMax) {
      selectedFiles.value = selectedFiles.value.slice(0, newMax)
      emitFilesChanged()
    }
  }
)

defineExpose({
  selectedFiles: () => selectedFiles.value.map(f => f.file),
  isEncoding,
  encodingProgress
})
</script>

<style scoped>
/* Animations */
.transition-all {
  transition: all 0.3s ease;
}
</style>
