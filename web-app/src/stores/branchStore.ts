import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useBranchStore = defineStore('branch', () => {
  const selectedBranch = ref<string>(getBranchNameFromLocalStorage())

  function getBranchNameFromLocalStorage(): string {
    try {
      const branchData = localStorage.getItem('selected_branch')
      if (branchData) {
        const parsed = JSON.parse(branchData)
        return parsed.name || ''
      }
    } catch (error) {
      console.error('Error parsing branch from localStorage:', error)
    }
    return ''
  }

  const setSelectedBranch = (branch: string) => {
    selectedBranch.value = branch
    localStorage.setItem('selected_branch', JSON.stringify({
      name: branch
    }))
  }

  const hasBranch = () => !!selectedBranch.value

  return {
    selectedBranch,
    setSelectedBranch,
    hasBranch
  }
})
