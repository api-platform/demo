import {
  fetch,
  normalize,
  extractHubURL,
  mercureSubscribe as subscribe
} from '../../utils/dataAccess';
import { success as deleteSuccess } from './delete';

export function error(error) {
  return { type: 'BOOK_LIST_ERROR', error };
}

export function loading(loading) {
  return { type: 'BOOK_LIST_LOADING', loading };
}

export function success(retrieved) {
  return { type: 'BOOK_LIST_SUCCESS', retrieved };
}

export function list(page = '/books') {
  return dispatch => {
    dispatch(loading(true));
    dispatch(error(''));

    fetch(page)
      .then(response =>
        response
          .json()
          .then(retrieved => ({ retrieved, hubURL: extractHubURL(response) }))
      )
      .then(({ retrieved, hubURL }) => {
        retrieved = normalize(retrieved);

        dispatch(loading(false));
        dispatch(success(retrieved));

        if (hubURL)
          dispatch(
            mercureSubscribe(
              hubURL,
              retrieved['hydra:member'].map(i => i['@id'])
            )
          );
      })
      .catch(e => {
        dispatch(loading(false));
        dispatch(error(e.message));
      });
  };
}

export function reset(eventSource) {
  return dispatch => {
    if (eventSource) eventSource.close();

    dispatch({ type: 'BOOK_LIST_RESET' });
    dispatch(deleteSuccess(null));
  };
}

export function mercureSubscribe(hubURL, topics) {
  return dispatch => {
    topics.forEach(topic => hubURL.searchParams.append('topic', topic));

    const eventSource = subscribe(hubURL);
    eventSource.onopen = () => dispatch(mercureOpen(eventSource));
    eventSource.onmessage = event =>
      dispatch(mercureMessage(normalize(JSON.parse(event.data))));
  };
}

export function mercureOpen(eventSource) {
  return { type: 'BOOK_LIST_MERCURE_OPEN', eventSource };
}

export function mercureMessage(retrieved) {
  return dispatch => {
    if (1 === Object.keys(retrieved).length) {
      // A displayed item has been deleted
      dispatch(list());
      return;
    }

    dispatch({ type: 'BOOK_LIST_MERCURE_MESSAGE', retrieved });
  };
}
