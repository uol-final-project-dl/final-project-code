import React, {useEffect, useState} from 'react'
import {useDispatch, useSelector} from "react-redux";
import './styles.less';

export default function ToastContainer() {
    const dispatch = useDispatch();
    const toast = useSelector((state: any) => state.generals.toast);
    const [prevToast, setPrevToast] = useState('');
    const toastTimeout: number = toast?.timeout ?? 3000;

    useEffect(() => {
        let toastTimeoutId: NodeJS.Timeout | null = null;

        if (toast !== null) {
            setPrevToast(toast.text)

            toastTimeoutId = setTimeout(function () {
                closeToast();
            }, toastTimeout);
        }

        return () => {
            if (toastTimeoutId) {
                clearTimeout(toastTimeoutId);
            }
        }
    }, [toast])

    function closeToast() {
        dispatch({type: 'STOP_GENERAL_TOAST'});
    }

    return (
        <div id={'react-toast'} className={'toast-container ' + (toast !== null ? ('toasting ' + toast.type) : '')}>
            <div className={'toast-text'}>
                {toast !== null ? toast.text : prevToast}
            </div>
            <div className={'toast-close'} onClick={() => closeToast()}>
                X
            </div>
        </div>
    )
}
