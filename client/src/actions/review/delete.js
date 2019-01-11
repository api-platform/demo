import { fetch } from '../../utils/dataAccess';

export function error(error) {
  return { type: 'REVIEW_DELETE_ERROR', error };
}

export function loading(loading) {
  return { type: 'REVIEW_DELETE_LOADING', loading };
}

export function success(deleted) {
  return { type: 'REVIEW_DELETE_SUCCESS', deleted };
}

export function del(item) {
  return dispatch => {
    dispatch(loading(true));

    return fetch(item['@id'], { method: 'DELETE' })
      .then(() => {
        dispatch(loading(false));
        dispatch(success(item));
      })
      .catch(e => {
        dispatch(loading(false));
        dispatch(error(e.message));
      });
  };
}
