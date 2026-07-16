
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'

export function useModal(modalId = 'my_modal_3') {
    const router = useRouter()
    const userStore = useUserStore()

    return () => {
        if (!userStore.isLoggedIn) {
            alert('로그인이 필요합니다.')
            router.push({ name: 'Login' })
            return
        }
        const modal = document.getElementById(modalId)
        if (modal?.showModal) modal.showModal()
    }
}
