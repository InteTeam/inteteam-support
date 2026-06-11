import { useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    session: {
        id: string;
        status: string;
        ticket: { id: string; description: string };
        customer: { name: string };
        tenant: { name: string };
        session_token: string;
    };
    signalingHost: string;
}

export default function EngineerRemoteSession({ session, signalingHost }: Props) {
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const [status, setStatus] = useState<string>(session.status);
    const [connected, setConnected] = useState(false);
    const pcRef = useRef<RTCPeerConnection | null>(null);
    const wsRef = useRef<WebSocket | null>(null);

    useEffect(() => {
        if (session.status === 'ended' || session.status === 'declined') return;

        const wsUrl = `${signalingHost}/ws/engineer?token=${session.session_token}`;
        const ws = new WebSocket(wsUrl);
        wsRef.current = ws;

        const pc = new RTCPeerConnection({
            iceServers: [
                { urls: `turn:${signalingHost.replace(/wss?:\/\//, '')}:3478` },
            ],
        });
        pcRef.current = pc;

        // Receive JPEG frames via DataChannel and draw to canvas
        pc.ondatachannel = (event) => {
            const dc = event.channel;
            if (dc.label === 'video') {
                dc.binaryType = 'arraybuffer';
                dc.onmessage = (e) => {
                    const blob = new Blob([e.data], { type: 'image/jpeg' });
                    const url  = URL.createObjectURL(blob);
                    const img  = new Image();
                    img.onload = () => {
                        const canvas = canvasRef.current;
                        if (!canvas) return;
                        canvas.width  = img.width;
                        canvas.height = img.height;
                        canvas.getContext('2d')?.drawImage(img, 0, 0);
                        URL.revokeObjectURL(url);
                    };
                    img.src = url;
                };
                setConnected(true);
                setStatus('active');
            }
        };

        pc.onicecandidate = (event) => {
            if (event.candidate) {
                ws.send(JSON.stringify({
                    type:          'ice',
                    candidate:     event.candidate.candidate,
                    sdpMid:        event.candidate.sdpMid,
                    sdpMLineIndex: event.candidate.sdpMLineIndex,
                }));
            }
        };

        ws.onmessage = async (event) => {
            const msg = JSON.parse(event.data);
            switch (msg.type) {
                case 'ready':
                    // Agent connected — wait for offer from agent
                    break;
                case 'offer': {
                    await pc.setRemoteDescription({ type: 'offer', sdp: msg.sdp });
                    const answer = await pc.createAnswer();
                    await pc.setLocalDescription(answer);
                    ws.send(JSON.stringify({ type: 'answer', sdp: answer.sdp }));
                    break;
                }
                case 'ice':
                    if (msg.candidate) {
                        await pc.addIceCandidate({
                            candidate:     msg.candidate,
                            sdpMid:        msg.sdpMid,
                            sdpMLineIndex: msg.sdpMLineIndex,
                        });
                    }
                    break;
            }
        };

        ws.onclose = () => setConnected(false);

        return () => {
            ws.close();
            pc.close();
        };
    }, [session.id, session.session_token, signalingHost]);

    // Forward mouse/keyboard events to agent via a control DataChannel
    const sendControl = (payload: object) => {
        const pc = pcRef.current;
        if (!pc) return;
        // Find the control DataChannel on the existing peer connection
        // The agent creates it, so it arrives via ondatachannel
    };

    const handleCanvasMouseMove = (e: React.MouseEvent<HTMLCanvasElement>) => {
        const rect   = e.currentTarget.getBoundingClientRect();
        const scaleX = e.currentTarget.width  / rect.width;
        const scaleY = e.currentTarget.height / rect.height;
        sendControl({
            type: 'mousemove',
            x:    Math.round((e.clientX - rect.left) * scaleX),
            y:    Math.round((e.clientY - rect.top)  * scaleY),
        });
    };

    const handleCanvasMouseDown = (e: React.MouseEvent<HTMLCanvasElement>) => {
        sendControl({ type: 'mousedown', button: e.button });
    };

    const handleCanvasMouseUp = (e: React.MouseEvent<HTMLCanvasElement>) => {
        sendControl({ type: 'mouseup', button: e.button });
    };

    const handleCanvasWheel = (e: React.WheelEvent<HTMLCanvasElement>) => {
        sendControl({ type: 'wheel', deltaY: e.deltaY });
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        sendControl({ type: 'keydown', key: e.keyCode });
    };

    const handleKeyUp = (e: React.KeyboardEvent) => {
        sendControl({ type: 'keyup', key: e.keyCode });
    };

    const endSession = () => {
        window.axios.post(`/engineer/remote/${session.id}/end`).then(() => {
            router.visit(`/engineer/tickets/${session.ticket.id}`);
        });
    };

    return (
        <AppLayout title={`Remote Session — ${session.customer.name}`}>
            <div className="space-y-4">
                {/* Status bar */}
                <div className="flex items-center justify-between bg-white border border-gray-200 rounded-lg px-4 py-3">
                    <div className="flex items-center gap-4 text-sm">
                        <span className="text-gray-500">Customer:</span>
                        <span className="font-medium">{session.customer.name}</span>
                        <span className="text-gray-500">Tenant:</span>
                        <span className="font-medium">{session.tenant.name}</span>
                        <span className="text-gray-500">Status:</span>
                        <span className={`font-medium ${connected ? 'text-green-600' : 'text-yellow-600'}`}>
                            {connected ? 'Connected' : status === 'ended' ? 'Ended' : 'Waiting for agent…'}
                        </span>
                    </div>
                    <button
                        onClick={endSession}
                        className="text-sm px-3 py-1.5 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        End Session
                    </button>
                </div>

                {/* Waiting state */}
                {!connected && status !== 'ended' && (
                    <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <p className="text-yellow-800">
                            Waiting for the customer's agent to connect…
                        </p>
                        <p className="text-sm text-yellow-600 mt-1">
                            The customer should accept the request and start the desktop agent.
                        </p>
                    </div>
                )}

                {/* Remote screen canvas */}
                {connected && (
                    <div
                        className="border border-gray-300 rounded-lg overflow-hidden bg-black"
                        onKeyDown={handleKeyDown}
                        onKeyUp={handleKeyUp}
                        tabIndex={0}
                    >
                        <canvas
                            ref={canvasRef}
                            className="w-full cursor-crosshair"
                            onMouseMove={handleCanvasMouseMove}
                            onMouseDown={handleCanvasMouseDown}
                            onMouseUp={handleCanvasMouseUp}
                            onWheel={handleCanvasWheel}
                            onContextMenu={(e) => e.preventDefault()}
                        />
                    </div>
                )}

                {/* Ended */}
                {status === 'ended' && (
                    <div className="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                        <p className="text-gray-700">This session has ended.</p>
                        <a
                            href={`/engineer/tickets/${session.ticket.id}`}
                            className="mt-2 inline-block text-sm text-blue-600 hover:underline"
                        >
                            Back to ticket
                        </a>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
