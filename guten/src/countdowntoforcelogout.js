import './countdowntoforcelogout.scss';

import apiFetch from '@wordpress/api-fetch';

import { Modal } from '@wordpress/components';

import {
	render,
	useState,
	useEffect
} from '@wordpress/element';

const CountdownToForceLogout = () => {

	const limit_sec = parseInt( countdowntoforcelogout_data.limit_sec );

	const [ currentPeriodTime, updatecurrentPeriodTime ] = useState( parseInt( countdowntoforcelogout_data.period_time ) );
	const [ currentCountdown, updatecurrentCountdown ] = useState( countdowntoforcelogout_data.countdown );

	const [ isOpen, setOpen ] = useState( Boolean( countdowntoforcelogout_data.limit_modal ) );
	const openModal = () => setOpen( true );
	const closeModal = () => setOpen( false );

	useEffect( () => {
		let timer = setInterval( () => {
			apiFetch( {
				path: 'rf/countdown_to_force_logout_api/token',
			} ).then( ( response ) => {
				//console.log( response );
				updatecurrentPeriodTime( response['period_time'] );
				updatecurrentCountdown( response['countdown'] );
				if ( limit_sec > currentPeriodTime ) {
					setOpen( true );
				}
			} );
		}, 60000 );
		return () => {
			clearInterval( timer );
		};
	}, [ currentPeriodTime, currentCountdown ] );

	const items = [];
	if ( isOpen ) {
		items.push(
			<>
				{ currentCountdown }
				<Modal
					title = { countdowntoforcelogout_data.modal_title }
					onRequestClose = { closeModal }
					isDismissible = { true }
					className="limit_over_modal_content"
				>
					{ currentCountdown }
					<p className="description">
						{ countdowntoforcelogout_data.description }
					</p>
				</Modal>
			</>
		);
	} else {
		items.push(
			<>
				{ currentCountdown }
			</>
		);
	}

	return (
		<>
			{ items }
		</>
	);

};

render(
	<CountdownToForceLogout />,
	document.getElementById( 'countdowntoforcelogout' )
);

