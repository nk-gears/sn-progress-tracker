import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { EventReportForm } from '@/types'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/sn-progress/api.php'

export const useEventReportStore = defineStore('eventReport', () => {
  const events = ref<any[]>([])
  const isLoading = ref(false)
  const error = ref<string | null>(null)
  const isSubmitting = ref(false)

  const fetchEvents = async (branch: string) => {
    isLoading.value = true
    error.value = null
    try {
      const url = `${API_BASE_URL}/event-reports?branch=${encodeURIComponent(branch)}`
      const response = await fetch(url)
      const data = await response.json()

      if (data.success) {
        events.value = data.data || []
      } else {
        error.value = data.message || 'Failed to load events'
      }
    } catch (err) {
      error.value = 'Failed to load events'
      console.error('Error fetching events:', err)
    } finally {
      isLoading.value = false
    }
  }

  const encodeFileToBase64 = (file: File): Promise<string> => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader()
      reader.onload = () => resolve(reader.result as string)
      reader.onerror = reject
      reader.readAsDataURL(file)
    })
  }

  const submitEventReport = async (form: EventReportForm) => {
    isSubmitting.value = true
    error.value = null
    try {
      // Encode photos to base64
      const photoPromises = form.photos.map(async (photo, i) => {
        const base64 = await encodeFileToBase64(photo)
        return {
          [`photo${i + 1}`]: base64,
          [`photo${i + 1}_type`]: photo.type
        }
      })
      const photoResults = await Promise.all(photoPromises)
      const photos = Object.assign({}, ...photoResults)

      // Encode video if present
      let videoData: any = {}
      if (form.video) {
        const base64 = await encodeFileToBase64(form.video)
        videoData = {
          video: base64,
          video_type: form.video.type
        }
      }

      const payload = {
        branch: form.branch,
        event_title: form.eventTitle,
        description: form.eventDescription,
        event_date: form.eventDate,
        event_time: form.eventTime,
        participants: form.participants,
        ...photos,
        ...videoData
      }

      const url = `${API_BASE_URL}/event-reports`
      const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })

      const data = await response.json()

      if (!data.success) {
        throw new Error(data.message || 'Submission failed')
      }

      return data
    } catch (err) {
      const errorMsg = err instanceof Error ? err.message : 'Submission failed'
      error.value = errorMsg
      throw err
    } finally {
      isSubmitting.value = false
    }
  }

  return {
    events,
    isLoading,
    error,
    isSubmitting,
    fetchEvents,
    submitEventReport
  }
})
