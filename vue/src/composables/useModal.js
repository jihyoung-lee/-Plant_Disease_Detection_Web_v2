
import { useRouter } from 'vue-router'
export function useModal(modalId = 'my_modal_3') {
    const router = useRouter()
    return () => {
        const token = localStorage.getItem('token')
        if (!token) {
            alert('로그인이 필요합니다.')
            router.push({ name: 'Login' })
            return
        }
        const modal = document.getElementById(modalId)
        if (modal?.showModal) modal.showModal()
    }
}
